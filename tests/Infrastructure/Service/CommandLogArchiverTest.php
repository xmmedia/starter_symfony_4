<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Service;

use App\Infrastructure\Service\CommandLogArchiver;
use App\Tests\BaseTestCase;
use Carbon\CarbonImmutable;
use Doctrine\DBAL\Connection;

class CommandLogArchiverTest extends BaseTestCase
{
    private string $path;

    protected function setUp(): void
    {
        $this->path = sys_get_temp_dir().'/command_log_archive_'.bin2hex(random_bytes(8));
        mkdir($this->path, 0o775, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->path.'/*') ?: [] as $file) {
            unlink($file);
        }
        if (is_dir($this->path)) {
            rmdir($this->path);
        }

        parent::tearDown();
    }

    public function testArchiveWritesRowsAndDeletesThem(): void
    {
        $rows = [$this->row(1), $this->row(2)];

        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('fetchAllAssociative')
            ->once()
            ->andReturn($rows);
        $connection->shouldReceive('fetchAllAssociative')
            ->once()
            ->andReturn([]);
        $connection->shouldReceive('quote')
            ->andReturnUsing(static fn (string $value): string => "'".$value."'");
        $connection->shouldReceive('executeStatement')
            ->once()
            ->withArgs(
                static fn (string $sql, array $params): bool => str_starts_with($sql, 'DELETE FROM `command_log`')
                    && 2 === $params['lastNo'],
            )
            ->andReturn(2);
        $connection->shouldReceive('executeStatement')
            ->once()
            ->andReturn(0);

        $file = $this->path.'/archive.sql.gz';

        $archived = new CommandLogArchiver($connection)
            ->archive($file, CarbonImmutable::now(), 1000);

        $this->assertSame(2, $archived);

        $contents = gzdecode(file_get_contents($file));
        $this->assertStringContainsString(
            'INSERT INTO `command_log` (`no`, `command_id`, `command`, `payload`, `metadata`, `sent_at`) VALUES',
            $contents,
        );
        $this->assertStringContainsString("(1, '".$rows[0]['command_id']."'", $contents);
        $this->assertStringContainsString("(2, '".$rows[1]['command_id']."'", $contents);
    }

    public function testArchiveWithoutDeleting(): void
    {
        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('fetchAllAssociative')
            ->once()
            ->andReturn([$this->row(1)]);
        $connection->shouldReceive('fetchAllAssociative')
            ->once()
            ->andReturn([]);
        $connection->shouldReceive('quote')
            ->andReturnUsing(static fn (string $value): string => "'".$value."'");
        $connection->shouldNotReceive('executeStatement');

        $file = $this->path.'/archive.sql.gz';

        $archived = new CommandLogArchiver($connection)
            ->archive($file, CarbonImmutable::now(), 1000, false);

        $this->assertSame(1, $archived);
        $this->assertFileExists($file);
    }

    public function testArchiveRemovesTheFileWhenThereAreNoRows(): void
    {
        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('fetchAllAssociative')
            ->once()
            ->andReturn([]);

        $file = $this->path.'/archive.sql.gz';

        $archived = new CommandLogArchiver($connection)
            ->archive($file, CarbonImmutable::now(), 1000);

        $this->assertSame(0, $archived);
        $this->assertFileDoesNotExist($file);
    }

    public function testArchiveRemovesTheFileWhenItFails(): void
    {
        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('fetchAllAssociative')
            ->once()
            ->andThrow(new \RuntimeException('Connection lost'));

        $file = $this->path.'/archive.sql.gz';

        try {
            new CommandLogArchiver($connection)->archive($file, CarbonImmutable::now(), 1000);
            $this->fail('An exception should have been thrown.');
        } catch (\RuntimeException) {
            $this->assertFileDoesNotExist($file);
        }
    }

    public function testCount(): void
    {
        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('fetchOne')
            ->once()
            ->withArgs(static fn (string $sql): bool => str_starts_with($sql, 'SELECT COUNT(*)'))
            ->andReturn('12');

        $this->assertSame(12, new CommandLogArchiver($connection)->count(CarbonImmutable::now()));
    }

    public function testImport(): void
    {
        $file = $this->archiveFile("INSERT INTO `command_log` (`no`) VALUES\n(1),\n(2);\n"
            ."INSERT INTO `command_log` (`no`) VALUES\n(3);\n");

        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('beginTransaction')
            ->once();
        $connection->shouldReceive('executeStatement')
            ->twice()
            ->withArgs(static fn (string $sql): bool => str_starts_with($sql, 'INSERT INTO `command_log`'))
            ->andReturn(2, 1);
        $connection->shouldReceive('commit')
            ->once();

        $this->assertSame([2, 3], new CommandLogArchiver($connection)->import($file));
    }

    public function testImportRollsBack(): void
    {
        $file = $this->archiveFile("INSERT INTO `command_log` (`no`) VALUES\n(1);\n");

        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('beginTransaction')
            ->once();
        $connection->shouldReceive('executeStatement')
            ->once()
            ->andThrow(new \RuntimeException('Duplicate entry'));
        $connection->shouldReceive('rollBack')
            ->once();
        $connection->shouldNotReceive('commit');

        $this->expectException(\RuntimeException::class);

        new CommandLogArchiver($connection)->import($file);
    }

    public function testImportDryRun(): void
    {
        $file = $this->archiveFile("INSERT INTO `command_log` (`no`) VALUES\n(1);\n");

        $connection = \Mockery::mock(Connection::class);
        $connection->shouldNotReceive('executeStatement');
        $connection->shouldNotReceive('beginTransaction');

        $this->assertSame([1, 0], new CommandLogArchiver($connection)->import($file, true));
    }

    public function testImportIncompleteStatement(): void
    {
        $file = $this->archiveFile("INSERT INTO `command_log` (`no`) VALUES\n(1),\n");

        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('beginTransaction')
            ->once();
        $connection->shouldReceive('rollBack')
            ->once();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageIsOrContains('ends with an incomplete statement');

        new CommandLogArchiver($connection)->import($file);
    }

    private function archiveFile(string $sql): string
    {
        $file = $this->path.'/archive.sql.gz';
        file_put_contents($file, gzencode("-- a comment\n\n".$sql));

        return $file;
    }

    /** @return array<string, mixed> */
    private function row(int $no): array
    {
        $faker = $this->faker();

        return [
            'no'         => $no,
            'command_id' => $faker->uuid(),
            'command'    => \App\Model\User\Command\AdminAddUser::class,
            'payload'    => '{"userId":"'.$faker->uuid().'"}',
            'metadata'   => '{"ipAddress":"'.$faker->ipv4().'"}',
            'sent_at'    => $faker->dateTime()->format('Y-m-d H:i:s.u'),
        ];
    }
}
