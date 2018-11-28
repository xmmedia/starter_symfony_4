<?php

declare(strict_types=1);

namespace App\Command;

use App\Infrastructure\Service\Supervisord;
use Psr\Container\ContainerInterface;
use Supervisor\Exception\Fault;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Assert\Assert;

final class SupervisorRunningCheckCommand extends Command
{
    /** @var Supervisord */
    private $supervisorClient;

    /** @var ContainerInterface */
    private $projectionManagersLocator;

    public function __construct(
        Supervisord $supervisorClient,
        ContainerInterface $projectionManagersLocator
    ) {
        parent::__construct();

        $this->supervisorClient = $supervisorClient;
        $this->projectionManagersLocator = $projectionManagersLocator;
    }

    protected function configure()
    {
        $this
            ->setName('app:supervisor:check')
            ->setDescription(
                'Checks that all Supervisor programs are running.'
            )
            ->addArgument(
                'manager',
                InputArgument::REQUIRED,
                'The name of the projection manager.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Checking Supervisor Programs');
        $io->text((new \DateTimeImmutable())->format('Y-m-d H:i:s'));

        $manager = $input->getArgument('manager');
        Assert::notEmpty($manager, 'The projection manager name is required.');

        $projectionManager = $this->projectionManagersLocator->get(
            'prooph_event_store.projection_manager.'.$manager
        );
        $projections = $projectionManager->fetchProjectionNames(null, 100);

        $checked = 0;
        $started = 0;

        foreach ($projections as $projection) {
            if (in_array($projection, Supervisord::$notInSupervisor)) {
                continue;
            }

            ++$checked;

            if (!$this->supervisorClient->isRunning($projection)) {
                $io->note(sprintf('Projection "%s" is not running. Attempting to restart.', $projection));

                try {
                    $this->supervisorClient->start($projection);
                    ++$started;
                } catch (Fault $e) {
                    $io->error(sprintf('Failed to restart projection "%s".', $projection));
                }
            }
        }

        if ($started > 0) {
            $io->success(sprintf(
                '%d projections were checked, %d were running, and %d were started successfully.',
                $checked,
                $checked - $started,
                $started
            ));
        } else {
            $io->success(sprintf(
                'All %d projections are running.',
                $checked
            ));
        }
    }
}
