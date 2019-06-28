<?php

declare(strict_types=1);

namespace App\Command;

use Prooph\EventStore\Pdo\Projection\PdoEventStoreProjector;
use Prooph\EventStore\Projection\ReadModelProjector;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ProjectionRunCommand extends Command
{
    protected const ARGUMENT_PROJECTION_NAME = 'projection-name';
    protected const OPTION_RUN_ONCE = 'run-once';
    protected const OPTION_SLEEP = 'sleep';

    /** @var ContainerInterface */
    private $projectionManagerForProjectionsLocator;

    /** @var ContainerInterface */
    protected $projectionsLocator;

    /** @var ContainerInterface */
    protected $projectionReadModelLocator;

    /** @var string */
    private $projectionName;

    /** @var ReadModelProjector */
    private $projector;

    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        ContainerInterface $projectionManagerForProjectionsLocator,
        ContainerInterface $projectionsLocator,
        ContainerInterface $projectionReadModelLocator
    ) {
        parent::__construct();

        $this->projectionManagerForProjectionsLocator = $projectionManagerForProjectionsLocator;
        $this->projectionsLocator = $projectionsLocator;
        $this->projectionReadModelLocator = $projectionReadModelLocator;
    }

    protected function configure()
    {
        $this
            ->setName('app:projection:run')
            ->setDescription('Runs a projection')
            ->addArgument(
                static::ARGUMENT_PROJECTION_NAME,
                InputArgument::REQUIRED,
                'The name of the Projection'
            )
            ->addOption(
                static::OPTION_RUN_ONCE,
                'o',
                InputOption::VALUE_NONE,
                'Loop the projection only once, then exit'
            )
            ->addOption(
                static::OPTION_SLEEP,
                's',
                InputOption::VALUE_REQUIRED,
                'The sleep time of the projector in microseconds',
                1000000 // 1 second
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->io->title('Running Projection');
        $this->io->text((new \DateTimeImmutable())->format('Y-m-d H:i:s'));

        $this->projectionName = $input->getArgument(static::ARGUMENT_PROJECTION_NAME);
        $keepRunning = !$input->getOption(static::OPTION_RUN_ONCE);
        $sleep = (int) $input->getOption(static::OPTION_SLEEP);

        if (!$this->projectionManagerForProjectionsLocator->has($this->projectionName)) {
            throw new \RuntimeException(
                sprintf('ProjectionManager for "%s" not found', $this->projectionName)
            );
        }
        /** @var \Prooph\EventStore\Projection\ProjectionManager $projectionManager */
        $projectionManager = $this->projectionManagerForProjectionsLocator
            ->get($this->projectionName);

        if (!$this->projectionsLocator->has($this->projectionName)) {
            throw new \RuntimeException(
                sprintf('Projection "%s" not found', $this->projectionName)
            );
        }
        /** @var \Prooph\Bundle\EventStore\Projection\ReadModelProjection $projection */
        $projection = $this->projectionsLocator->get($this->projectionName);

        if (!$this->projectionReadModelLocator->has($this->projectionName)) {
            throw new \RuntimeException(
                sprintf('ReadModel for "%s" not found', $this->projectionName)
            );
        }
        /** @var \Prooph\EventStore\Projection\ReadModel $readModel */
        $readModel = $this->projectionReadModelLocator->get($this->projectionName);

        $this->projector = $projection->project(
            $projectionManager->createReadModelProjection($this->projectionName, $readModel, [
                PdoEventStoreProjector::OPTION_SLEEP          => $sleep,
                PdoEventStoreProjector::OPTION_PCNTL_DISPATCH => true,
            ])
        );

        $this->io->text(sprintf('Initialized projection "%s"', $this->projectionName));

        try {
            $state = $projectionManager->fetchProjectionStatus($this->projectionName)->getValue();
        } catch (\Prooph\EventStore\Exception\RuntimeException $e) {
            $state = 'unknown';
        }
        $this->io->text(sprintf('Current status: %s', $state));

        $this->io->text(sprintf('Starting projection "%s"', $this->projectionName));
        $this->io->text(sprintf('Keep running %s', true === $keepRunning ? 'enabled' : 'disabled'));

        pcntl_signal(SIGTERM, [$this, 'signalHandler']);
        pcntl_signal(SIGHUP, [$this, 'signalHandler']);
        pcntl_signal(SIGINT, [$this, 'signalHandler']);
        pcntl_signal(SIGQUIT, [$this, 'signalHandler']);

        $this->projector->run((bool) $keepRunning);

        $this->io->success(sprintf('Projection %s completed.', $this->projectionName));
    }

    public function signalHandler(): void
    {
        $this->io->success(sprintf('Projection %s stopped.', $this->projectionName));
        $this->projector->stop();
    }
}
