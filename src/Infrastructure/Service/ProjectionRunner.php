<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use Prooph\Bundle\EventStore\Projection\ReadModelProjection;
use Prooph\EventStore\Projection\ProjectionManager;
use Prooph\EventStore\Projection\ProjectionStatus;
use Prooph\EventStore\Projection\ReadModel;
use Prooph\EventStore\Projection\ReadModelProjector;
use Psr\Container\ContainerInterface;

class ProjectionRunner
{
    /** @var ContainerInterface */
    private $projectionManagerForProjectionsLocator;

    /** @var ContainerInterface */
    private $projectionsLocator;

    /** @var ContainerInterface */
    private $projectionReadModelLocator;

    /** @var string */
    private $projectionName;

    /** @var ProjectionManager */
    private $projectionManager;

    /** @var ReadModelProjector */
    private $projector;

    /** @var ProjectionManager */
    private $projectionsManager;

    public function __construct(
        ProjectionManager $projectionsManager,
        ContainerInterface $projectionManagerForProjectionsLocator,
        ContainerInterface $projectionsLocator,
        ContainerInterface $projectionReadModelLocator
    ) {
        $this->projectionsManager = $projectionsManager;
        $this->projectionManagerForProjectionsLocator = $projectionManagerForProjectionsLocator;
        $this->projectionsLocator = $projectionsLocator;
        $this->projectionReadModelLocator = $projectionReadModelLocator;
    }

    public function run(
        string $projectionName,
        bool $keepRunning = false,
        array $readModelProjectionOptions = []
    ): void {
        $this->projectionName = $projectionName;

        $this->configure($projectionName, $readModelProjectionOptions);

        try {
            $state = $this->state();

            if ($state->is(ProjectionStatus::IDLE())) {
                $this->projector->run($keepRunning);
            } else {
                throw new \RuntimeException(
                    sprintf(
                        'Projection "%s" is not idle. It\'s state is "%s"',
                        $projectionName,
                        $state->getValue()
                    )
                );
            }
        } catch (\Prooph\EventStore\Exception\ProjectionNotFound $e) {
            // try running
            // the likely case is the projection has not been initialized
            $this->projector->run($keepRunning);
        } catch (\Prooph\EventStore\Exception\RuntimeException $e) {
            throw new \RuntimeException(
                sprintf(
                    'Projection "%s" is not idle. It\'s state is "%s"',
                    $projectionName,
                    'unknown'
                )
            );
        }
    }

    public function configure(
        string $projectionName,
        array $readModelProjectionOptions = []
    ): ReadModelProjector {
        $this->projectionName = $projectionName;

        if (!$this->projectionManagerForProjectionsLocator->has($this->projectionName)) {
            throw new \RuntimeException(
                sprintf('ProjectionManager for "%s" not found', $this->projectionName)
            );
        }
        $this->projectionManager = $this->projectionManagerForProjectionsLocator
            ->get($this->projectionName);

        if (!$this->projectionsLocator->has($this->projectionName)) {
            throw new \RuntimeException(
                sprintf('Projection "%s" not found', $this->projectionName)
            );
        }
        /** @var ReadModelProjection $projection */
        $projection = $this->projectionsLocator->get($this->projectionName);

        if (!$this->projectionReadModelLocator->has($this->projectionName)) {
            throw new \RuntimeException(
                sprintf('ReadModel for "%s" not found', $this->projectionName)
            );
        }
        /** @var ReadModel $readModel */
        $readModel = $this->projectionReadModelLocator->get($this->projectionName);

        $this->projector = $projection->project(
            $this->projectionManager->createReadModelProjection(
                $this->projectionName,
                $readModel,
                $readModelProjectionOptions
            )
        );

        return $this->projector;
    }

    public function state(): ProjectionStatus
    {
        return $this->projectionManager->fetchProjectionStatus(
            $this->projectionName
        );
    }

    public function getAllProjectionNames(int $limit = 20): array
    {
        return $this->projectionsManager->fetchProjectionNames(null, $limit);
    }
}
