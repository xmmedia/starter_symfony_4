<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use Carbon\CarbonImmutable;
use Doctrine\DBAL\Connection;
use Xm\SymfonyBundle\Doctrine\Types\DateTimeMicrosecondsType;

/**
 * Moves old rows out of `command_log` into a gzipped SQL file & back again.
 *
 * `command_log` records every command payload, so it grows without bound & is
 * only ever read for auditing. The rows are written out as INSERT statements so
 * they can be loaded back into the table (or any MySQL server) as-is.
 */
class CommandLogArchiver
{
    public const string TABLE = 'command_log';
    private const array COLUMNS = [
        'no',
        'command_id',
        'command',
        'payload',
        'metadata',
        'sent_at',
    ];

    public function __construct(private readonly Connection $connection)
    {
    }

    public function count(CarbonImmutable $cutoff): int
    {
        return (int) $this->connection->fetchOne(
            \sprintf('SELECT COUNT(*) FROM `%s` WHERE `sent_at` < :sentAt', self::TABLE),
            ['sentAt' => $cutoff],
            ['sentAt' => DateTimeMicrosecondsType::TYPENAME],
        );
    }

    /**
     * Writes the rows sent before $cutoff to $file, then deletes them.
     *
     * The file is removed again when there was nothing to archive, so an empty
     * archive is never left behind.
     *
     * @return int the number of rows archived
     */
    public function archive(
        string $file,
        CarbonImmutable $cutoff,
        int $batchSize,
        bool $delete = true,
    ): int {
        $handle = gzopen($file, 'wb9');
        if (false === $handle) {
            throw new \RuntimeException(\sprintf('Unable to open "%s" for writing.', $file));
        }

        try {
            [$archived, $lastNo] = $this->write($handle, $cutoff, $batchSize);
        } catch (\Throwable $e) {
            gzclose($handle);
            unlink($file);

            throw $e;
        }

        // the rows are only on disk once the stream is closed, so nothing is
        // deleted until this has happened
        gzclose($handle);

        if (0 === $archived) {
            unlink($file);

            return 0;
        }

        if ($delete) {
            $this->delete($cutoff, $lastNo, $batchSize);
        }

        return $archived;
    }

    /**
     * Runs the statements in an archive, in one transaction so a failure part
     * way through doesn't leave the table half loaded.
     *
     * @return array{0: int, 1: int} the number of statements run & rows imported
     */
    public function import(string $file, bool $dryRun = false): array
    {
        // gzopen reads uncompressed files too, so a decompressed archive works
        $handle = gzopen($file, 'rb');
        if (false === $handle) {
            throw new \RuntimeException(\sprintf('Unable to open "%s" for reading.', $file));
        }

        try {
            if ($dryRun) {
                return [iterator_count($this->statements($handle, $file)), 0];
            }

            $this->connection->beginTransaction();

            try {
                $statements = 0;
                $rows = 0;

                foreach ($this->statements($handle, $file) as $statement) {
                    ++$statements;
                    $rows += $this->connection->executeStatement($statement);
                }

                $this->connection->commit();
            } catch (\Throwable $e) {
                $this->connection->rollBack();

                throw $e;
            }

            return [$statements, $rows];
        } finally {
            gzclose($handle);
        }
    }

    /**
     * @param resource $handle
     *
     * @return array{0: int, 1: int} the number of rows written & the highest `no` written
     */
    private function write($handle, CarbonImmutable $cutoff, int $batchSize): array
    {
        gzwrite($handle, $this->header($cutoff));

        $query = \sprintf(
            'SELECT `%s` FROM `%s` WHERE `sent_at` < :sentAt AND `no` > :lastNo ORDER BY `no` LIMIT %d',
            implode('`, `', self::COLUMNS),
            self::TABLE,
            $batchSize,
        );

        $archived = 0;
        $lastNo = 0;

        while (true) {
            $rows = $this->connection->fetchAllAssociative(
                $query,
                ['sentAt' => $cutoff, 'lastNo' => $lastNo],
                ['sentAt' => DateTimeMicrosecondsType::TYPENAME],
            );

            if ([] === $rows) {
                return [$archived, $lastNo];
            }

            gzwrite($handle, $this->insert($rows));

            $lastNo = (int) array_last($rows)['no'];
            $archived += \count($rows);
        }
    }

    private function delete(CarbonImmutable $cutoff, int $lastNo, int $batchSize): void
    {
        $query = \sprintf(
            'DELETE FROM `%s` WHERE `sent_at` < :sentAt AND `no` <= :lastNo LIMIT %d',
            self::TABLE,
            $batchSize,
        );

        do {
            $deleted = $this->connection->executeStatement(
                $query,
                ['sentAt' => $cutoff, 'lastNo' => $lastNo],
                ['sentAt' => DateTimeMicrosecondsType::TYPENAME],
            );
        } while (0 < $deleted);
    }

    /**
     * @param resource $handle
     *
     * @return \Generator<int, string>
     */
    private function statements($handle, string $file): \Generator
    {
        $statement = '';

        while (false !== ($line = gzgets($handle))) {
            $line = rtrim($line);

            if ('' === $line || str_starts_with($line, '--')) {
                continue;
            }

            $statement .= $line."\n";

            // every row the archive writes is on its own line, so a line ending
            // with a semicolon always ends the statement
            if (str_ends_with($line, ';')) {
                yield $statement;

                $statement = '';
            }
        }

        if ('' !== trim($statement)) {
            throw new \RuntimeException(
                \sprintf('"%s" ends with an incomplete statement.', $file),
            );
        }
    }

    private function header(CarbonImmutable $cutoff): string
    {
        return \sprintf(
            "-- Archive of `%s` rows sent before %s\n-- Created %s\n\n",
            self::TABLE,
            $cutoff->format('Y-m-d H:i:s.u'),
            CarbonImmutable::now()->format('Y-m-d H:i:s'),
        );
    }

    /** @param list<array<string, mixed>> $rows */
    private function insert(array $rows): string
    {
        return \sprintf(
            "INSERT INTO `%s` (`%s`) VALUES\n%s;\n",
            self::TABLE,
            implode('`, `', self::COLUMNS),
            implode(
                ",\n",
                array_map(
                    fn (array $row): string => \sprintf('(%s)', implode(', ', array_map($this->value(...), $row))),
                    $rows,
                ),
            ),
        );
    }

    private function value(mixed $value): string
    {
        if (null === $value) {
            return 'NULL';
        }

        if (\is_int($value)) {
            return (string) $value;
        }

        return $this->connection->quote((string) $value);
    }
}
