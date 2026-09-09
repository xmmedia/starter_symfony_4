<?php

declare(strict_types=1);

namespace App\Command;

use App\Infrastructure\Service\CommandLogArchiver;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:command-log:archive',
    description: 'Archive old command_log rows to a gzipped SQL file, or import one back.',
)]
final class ArchiveCommandLogCommand extends Command
{
    private const string OLDER_THAN = 'older-than';
    private const string IMPORT = 'import';
    private const string PATH = 'path';
    private const string BATCH_SIZE = 'batch-size';
    private const string KEEP = 'keep';
    private const string DRY_RUN = 'dry-run';

    public function __construct(private readonly CommandLogArchiver $archiver)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                self::OLDER_THAN,
                InputArgument::OPTIONAL,
                'Archive rows older than this: a number of days (90),'
                .' an ISO 8601 period (P6M) or a readable interval ("6 months")',
                '1 year',
            )
            ->addOption(
                self::IMPORT,
                null,
                InputOption::VALUE_REQUIRED,
                'Import an archive back into the table instead of creating one',
            )
            ->addOption(
                self::PATH,
                null,
                InputOption::VALUE_REQUIRED,
                'Directory to write the archive to, defaults to the current directory',
            )
            ->addOption(
                self::BATCH_SIZE,
                null,
                InputOption::VALUE_REQUIRED,
                'Number of rows read, written & deleted at a time',
                '1000',
            )
            ->addOption(
                self::KEEP,
                null,
                InputOption::VALUE_NONE,
                'Write the archive but don\'t delete the rows',
            )
            ->addOption(
                self::DRY_RUN,
                null,
                InputOption::VALUE_NONE,
                'Report what would be archived without writing or deleting anything',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption(self::DRY_RUN);

        if (null !== $input->getOption(self::IMPORT)) {
            return $this->import((string) $input->getOption(self::IMPORT), $dryRun, $io);
        }

        $batchSize = (int) $input->getOption(self::BATCH_SIZE);
        if (1 > $batchSize) {
            throw new InvalidArgumentException('The batch size must be at least 1.');
        }

        $cutoff = $this->cutoff((string) $input->getArgument(self::OLDER_THAN));

        $io->comment(\sprintf('Archiving rows sent before %s', $cutoff->format('Y-m-d H:i:s')));

        if ($dryRun) {
            $io->success(\sprintf('%s row(s) would be archived.', number_format($this->archiver->count($cutoff))));

            return Command::SUCCESS;
        }

        $file = $this->file((string) ($input->getOption(self::PATH) ?? getcwd()), $cutoff);
        $delete = !$input->getOption(self::KEEP);

        $archived = $this->archiver->archive($file, $cutoff, $batchSize, $delete);

        if (0 === $archived) {
            $io->success('No rows to archive.');

            return Command::SUCCESS;
        }

        $io->success(\sprintf(
            '%s row(s) %s to %s (%s).',
            number_format($archived),
            $delete ? 'archived' : 'copied',
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

    private function cutoff(string $olderThan): CarbonImmutable
    {
        if (ctype_digit($olderThan)) {
            return CarbonImmutable::now()->subDays((int) $olderThan);
        }

        try {
            $interval = CarbonInterval::make($olderThan);
        } catch (\Exception $e) {
            throw $this->invalidOlderThan($olderThan, $e);
        }

        if (null === $interval) {
            throw $this->invalidOlderThan($olderThan);
        }

        return CarbonImmutable::now()->sub($interval->abs());
    }

    private function invalidOlderThan(string $olderThan, ?\Throwable $previous = null): InvalidArgumentException
    {
        return new InvalidArgumentException(
            \sprintf('"%s" is not a number of days or a valid interval.', $olderThan),
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
            $cutoff->format('Y-m-d'),
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
