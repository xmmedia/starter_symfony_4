<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\ArchiveCommandLogCommand;
use App\Infrastructure\Service\CommandLogArchiver;
use App\Tests\BaseTestCase;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Tester\CommandTester;

class ArchiveCommandLogCommandTest extends BaseTestCase
{
    private string $path;

    protected function setUp(): void
    {
        $this->path = sys_get_temp_dir().'/command_log_archive_'.bin2hex(random_bytes(8));
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

    public function testArchive(): void
    {
        $archiver = \Mockery::mock(CommandLogArchiver::class);
        $archiver->shouldReceive('archive')
            ->once()
            ->withArgs(static function (string $file, CarbonImmutable $cutoff, int $batchSize, bool $delete): bool {
                file_put_contents($file, gzencode('-- archive'));

                return str_starts_with(basename($file), 'command_log_before_')
                    && 1000 === $batchSize
                    && $delete;
            })
            ->andReturn(2);

        $commandTester = new CommandTester(
            new ArchiveCommandLogCommand($archiver),
        );

        $result = $commandTester->execute(['older-than' => '90', '--path' => $this->path]);

        $this->assertSame(Command::SUCCESS, $result);
        $this->assertStringContainsString('2 row(s) archived to', $commandTester->getDisplay());
    }

    public function testKeepDoesNotDelete(): void
    {
        $archiver = \Mockery::mock(CommandLogArchiver::class);
        $archiver->shouldReceive('archive')
            ->once()
            ->withArgs(static function (string $file, CarbonImmutable $cutoff, int $batchSize, bool $delete): bool {
                file_put_contents($file, gzencode('-- archive'));

                return !$delete;
            })
            ->andReturn(1);

        $commandTester = new CommandTester(
            new ArchiveCommandLogCommand($archiver),
        );

        $result = $commandTester->execute(['--keep' => true, '--path' => $this->path]);

        $this->assertSame(Command::SUCCESS, $result);
        $this->assertStringContainsString('1 row(s) copied to', $commandTester->getDisplay());
    }

    public function testNoRows(): void
    {
        $archiver = \Mockery::mock(CommandLogArchiver::class);
        $archiver->shouldReceive('archive')
            ->once()
            ->andReturn(0);

        $commandTester = new CommandTester(
            new ArchiveCommandLogCommand($archiver),
        );

        $result = $commandTester->execute(['--path' => $this->path]);

        $this->assertSame(Command::SUCCESS, $result);
        $this->assertStringContainsString('No rows to archive', $commandTester->getDisplay());
    }

    public function testDryRun(): void
    {
        $archiver = \Mockery::mock(CommandLogArchiver::class);
        $archiver->shouldReceive('count')
            ->once()
            ->andReturn(12);
        $archiver->shouldNotReceive('archive');

        $commandTester = new CommandTester(
            new ArchiveCommandLogCommand($archiver),
        );

        $result = $commandTester->execute(['--dry-run' => true]);

        $this->assertSame(Command::SUCCESS, $result);
        $this->assertStringContainsString('12 row(s) would be archived', $commandTester->getDisplay());
        $this->assertDirectoryDoesNotExist($this->path);
    }

    /** @return list<array{0: string, 1: string}> */
    public static function olderThanProvider(): array
    {
        return [
            ['90', '-90 days'],
            ['P6M', '-6 months'],
            ['6 months', '-6 months'],
            ['1 year', '-1 year'],
        ];
    }

    #[DataProvider('olderThanProvider')]
    public function testOlderThanFormats(string $olderThan, string $expected): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::now());

        $archiver = \Mockery::mock(CommandLogArchiver::class);
        $archiver->shouldReceive('count')
            ->once()
            ->andReturn(0);

        $commandTester = new CommandTester(
            new ArchiveCommandLogCommand($archiver),
        );

        $commandTester->execute(['older-than' => $olderThan, '--dry-run' => true]);

        $this->assertStringContainsString(
            CarbonImmutable::now()->modify($expected)->format('Y-m-d H:i:s'),
            $commandTester->getDisplay(),
        );

        CarbonImmutable::setTestNow();
    }

    public function testInvalidOlderThan(): void
    {
        $commandTester = new CommandTester(
            new ArchiveCommandLogCommand(\Mockery::mock(CommandLogArchiver::class)),
        );

        $this->expectException(InvalidArgumentException::class);

        $commandTester->execute(['older-than' => $this->faker()->word()]);
    }

    public function testInvalidBatchSize(): void
    {
        $commandTester = new CommandTester(
            new ArchiveCommandLogCommand(\Mockery::mock(CommandLogArchiver::class)),
        );

        $this->expectException(InvalidArgumentException::class);

        $commandTester->execute(['--batch-size' => '0']);
    }

    public function testImport(): void
    {
        $file = $this->archiveFile();

        $archiver = \Mockery::mock(CommandLogArchiver::class);
        $archiver->shouldReceive('import')
            ->once()
            ->with($file, false)
            ->andReturn([2, 3]);

        $commandTester = new CommandTester(
            new ArchiveCommandLogCommand($archiver),
        );

        $result = $commandTester->execute(['--import' => $file]);

        $this->assertSame(Command::SUCCESS, $result);
        $this->assertStringContainsString('3 row(s) imported into `command_log`', $commandTester->getDisplay());
    }

    public function testImportDryRun(): void
    {
        $file = $this->archiveFile();

        $archiver = \Mockery::mock(CommandLogArchiver::class);
        $archiver->shouldReceive('import')
            ->once()
            ->with($file, true)
            ->andReturn([1, 0]);

        $commandTester = new CommandTester(
            new ArchiveCommandLogCommand($archiver),
        );

        $result = $commandTester->execute(['--import' => $file, '--dry-run' => true]);

        $this->assertSame(Command::SUCCESS, $result);
        $this->assertStringContainsString('1 statement(s) would be run', $commandTester->getDisplay());
    }

    public function testImportMissingFile(): void
    {
        $archiver = \Mockery::mock(CommandLogArchiver::class);
        $archiver->shouldNotReceive('import');

        $commandTester = new CommandTester(
            new ArchiveCommandLogCommand($archiver),
        );

        $this->expectException(InvalidArgumentException::class);

        $commandTester->execute(['--import' => $this->path.'/missing.sql.gz']);
    }

    private function archiveFile(): string
    {
        mkdir($this->path, 0o775, true);

        $file = $this->path.'/archive.sql.gz';
        file_put_contents($file, gzencode("-- a comment\n"));

        return $file;
    }
}
