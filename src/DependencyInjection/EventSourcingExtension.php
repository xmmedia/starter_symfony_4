<?php

declare(strict_types=1);

namespace App\DependencyInjection;

use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

class EventSourcingExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(
            $this->getConfiguration($configs, $container),
            $configs
        );

        if (!empty($config['repositories'])) {
            $this->loadRepositories($config, $container);
        }
    }

    private function loadRepositories(
        array $config,
        ContainerBuilder $container
    ): void {
        if (!empty($config['repositories'])) {
            foreach ($config['repositories'] as $repositoryName => $repositoryConfig) {
                $repositoryClass = $repositoryConfig['repository_class'] ?? $repositoryName;
                $eventStoreId = 'prooph_event_store.'.$repositoryConfig['store'];

                if (!class_exists($repositoryClass)) {
                    throw new \RuntimeException(sprintf(
                        'You must configure the class of repository "%s" either by configuring the \'repository_class\' key or by directly using the FQCN as the repository key.',
                        $repositoryClass
                    ));
                }

                $container
                    ->setDefinition(
                        $repositoryName,
                        new ChildDefinition('App\EventSourcing\Aggregate\AggregateRepository')
                    )
                    ->setArguments(
                        [
                            $repositoryClass,
                            new Reference($eventStoreId),
                            $repositoryConfig['aggregate_type'],
                            new Reference($repositoryConfig['aggregate_translator']),
                            $repositoryConfig['stream_name'],
                            $repositoryConfig['one_stream_per_aggregate'],
                            $repositoryConfig['disable_identity_map'] ?? false,
                        ]
                    );
            }
        }
    }
}
