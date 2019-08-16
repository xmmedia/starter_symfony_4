<?php

declare(strict_types=1);

namespace App\Command;

use App\Infrastructure\Service\Supervisord;
use Psr\Container\ContainerInterface;
use Supervisor\Exception\Fault;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class SupervisorRestartCommand extends Command
{
    /** @var Supervisord */
    private $supervisordClient;

    /** @var ContainerInterface */
    private $projectionManagersLocator;

    public function __construct(
        Supervisord $supervisordClient,
        ContainerInterface $projectionManagersLocator
    ) {
        parent::__construct();

        $this->supervisordClient = $supervisordClient;
        $this->projectionManagersLocator = $projectionManagersLocator;
    }

    protected function configure()
    {
        $this
            ->setName('app:supervisor:restart')
            ->setDescription(
                'Restarts all or a specific Supervisor program. This will also reload the config.'
            )
            ->addArgument(
                'projection',
                InputArgument::OPTIONAL,
                'The projection to restart (as "name_projection").'
            )
            ->addOption(
                'manager',
                null,
                InputOption::VALUE_REQUIRED,
                'The name of the projection manager.',
                'app'
            )
            ->addOption(
                'all',
                null,
                InputOption::VALUE_NONE,
                'Restart all Supervisor programs.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Restart Supervisor Programs');
        $io->text((new \DateTimeImmutable())->format('Y-m-d H:i:s'));

        if ($input->getOption('all')) {
            $this->supervisordClient->client()->reloadConfig();
            $this->supervisordClient->client()
                ->stopProcessGroup($this->supervisordClient->programPrefix())
            ;
            $this->supervisordClient->client()
                ->removeProcessGroup($this->supervisordClient->programPrefix())
            ;
            $this->supervisordClient->client()
                ->addProcessGroup($this->supervisordClient->programPrefix())
            ;

            $io->success('All Supervisor programs have been restarted successfully.');

            return;
        }

        $projectionManager = $this->projectionManagersLocator->get(
            'prooph_event_store.projection_manager.'.$input->getOption('manager')
        );
        $projections = $projectionManager->fetchProjectionNames(null, 100);

        $projection = $input->getArgument('projection');

        if (empty($projection)) {
            throw new \InvalidArgumentException(
                'A projection name is required or --all.'
            );
        }
        if (!\in_array($projection, $projections)) {
            // try again after adding "_projection"
            $projection = $projection.'_projection';

            if (!\in_array($projection, $projections)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'The projection "%s" cannot be found. Available projections are: %s',
                        $projection,
                        implode(', ', $projections)
                    )
                );
            }
        }
        if (\in_array($projection, Supervisord::$notInSupervisor)) {
            throw new \InvalidArgumentException(
                sprintf('The projection "%s" is not run by Supervisor.', $projection)
            );
        }

        $io->text(sprintf('Restarting projection "%s"', $projection));

        try {
            if ($this->supervisordClient->isRunning($projection)) {
                $this->supervisordClient->stop($projection);
            }

            $this->supervisordClient->start($projection);
        } catch (Fault $e) {
            $io->error(
                sprintf(
                    'Failed to stop or restart projection "%s".',
                    $projection
                )
            );
        }

        $io->success(sprintf(
            '%s has been restarted successfully.',
            $projection
        ));
    }
}
