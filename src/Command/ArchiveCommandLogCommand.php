<?php

declare(strict_types=1);

namespace App\Command;

use App\Infrastructure\Service\CommandLogArchiver;
use Carbon\CarbonImmutable;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:command-log:archive',
    description: 'Archive old command_log rows to a gzipped SQL file, or import one back.',
)]
final readonly class ArchiveCommandLogCommand
{
    private const string DATE_FORMAT = 'Y-m-d';

    public function __construct(private CommandLogArchiver $archiver)
    {
    }

    public function __invoke(
        SymfonyStyle $io,
        // required to archive, but not to import, so it's checked below
        #[Argument(
            'Archive rows sent before midnight UTC on this date (YYYY-MM-DD), required unless --import is used',
        )]
        ?string $before = null,
        #[Option('Import an archive back into the table instead of creating one')]
        ?string $import = null,
        #[Option('Directory to write the archive to, defaults to the current directory')]
        ?string $path = null,
        #[Option('Number of rows read, written & deleted at a time')]
        int $batchSize = 1000,
        #[Option('Write the archive but don\'t delete the rows')]
        bool $keep = false,
        #[Option('Report what would be archived without writing or deleting anything')]
        bool $dryRun = false,
    ): int {
        if (null !== $import) {
            return $this->import($import, $dryRun, $io);
        }

        if (1 > $batchSize) {
            throw new InvalidArgumentException('The batch size must be at least 1.');
        }

        if (null === $before) {
            throw new InvalidArgumentException('The "before" date is required to archive.');
        }

        $cutoff = $this->cutoff($before);

        if ($dryRun) {
            $io->comment(\sprintf('Archiving rows sent before %s UTC', $cutoff->toDateTimeString()));
            $io->success(\sprintf('%s row(s) would be archived.', number_format($this->archiver->count($cutoff))));

            return Command::SUCCESS;
        }

        if (!$io->confirm(\sprintf(
            '%s all rows sent before %s UTC?',
            $keep ? 'Archive' : 'Archive & delete',
            $cutoff->toDateTimeString(),
        ))) {
            $io->comment('Aborted.');

            return Command::SUCCESS;
        }

        $file = $this->file($path ?? (string) getcwd(), $cutoff);

        $archived = $this->archiver->archive($file, $cutoff, $batchSize, !$keep);

        if (0 === $archived) {
            $io->success('No rows to archive.');

            return Command::SUCCESS;
        }

        $io->success(\sprintf(
            '%s row(s) %s to %s (%s).',
            number_format($archived),
            $keep ? 'copied' : 'archived',
            $file,
            $this->formatBytes((int) filesize($file)),
        ));

        return Command::SUCCESS;
    }

    private function import(string $file, bool $dryRun, SymfonyStyle $io): int
    {
        if (!is_file($file)) {
            throw new InvalidArgumentException(\sprintf('The file "%s" doesn\'t exist.', $file));
        }

        $io->comment(\sprintf('Importing %s', $file));

        [$statements, $rows] = $this->archiver->import($file, $dryRun);

        if ($dryRun) {
            $io->success(\sprintf('%s statement(s) would be run.', number_format($statements)));

            return Command::SUCCESS;
        }

        $io->success(\sprintf(
            '%s row(s) imported into `%s`.',
            number_format($rows),
            CommandLogArchiver::TABLE,
        ));

        return Command::SUCCESS;
    }

    /**
     * Midnight UTC on the given date, so the whole of the day before it is archived.
     */
    private function cutoff(string $before): CarbonImmutable
    {
        // strictly Y-m-d: a relative interval ("6 months") would otherwise be
        // read as a date in the future & archive the whole table
        try {
            $cutoff = CarbonImmutable::createFromFormat('!'.self::DATE_FORMAT, $before, 'UTC');
        } catch (\Exception $e) {
            throw $this->invalidDate($before, $e);
        }

        // a rolled over date (2026-02-31) doesn't format back to what was given
        if (null === $cutoff || $before !== $cutoff->format(self::DATE_FORMAT)) {
            throw $this->invalidDate($before);
        }

        return $cutoff;
    }

    private function invalidDate(string $before, ?\Throwable $previous = null): InvalidArgumentException
    {
        return new InvalidArgumentException(
            \sprintf('"%s" is not a date in the format %s.', $before, self::DATE_FORMAT),
            0,
            $previous,
        );
    }

    private function file(string $path, CarbonImmutable $cutoff): string
    {
        if (!is_dir($path) && !mkdir($path, 0o775, true) && !is_dir($path)) {
            throw new \RuntimeException(\sprintf('Unable to create the directory "%s".', $path));
        }

        return \sprintf(
            '%s/%s_before_%s_%s.sql.gz',
            rtrim($path, '/'),
            CommandLogArchiver::TABLE,
            $cutoff->toDateString(),
            CarbonImmutable::now()->format('YmdHis'),
        );
    }

    private function formatBytes(int $bytes): string
    {
        $size = (float) $bytes;

        foreach (['B', 'KB', 'MB', 'GB'] as $unit) {
            if (1024 > $size) {
                return \sprintf('%.1f %s', $size, $unit);
            }

            $size /= 1024;
        }

        return \sprintf('%.1f TB', $size);
    }
}
