<?php

declare(strict_types=1);

namespace App\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is very similar to to:
 * https://github.com/prooph/event-store-symfony-bundle/blob/master/src/DependencyInjection/Configuration.php#L107.
 */
final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('event_sourcing');
        $rootNode = $treeBuilder->getRootNode();

        $this->addEventStoreSection($rootNode);

        return $treeBuilder;
    }

    /**
     * Add event store section to configuration tree.
     */
    private function addEventStoreSection(ArrayNodeDefinition $node): void
    {
        $treeBuilder = new TreeBuilder('repositories');
        $repositoriesNode = $treeBuilder->getRootNode();

        $beginsWithAt = function (string $v): bool {
            return 0 === strpos($v, '@');
        };
        $removeFirstCharacter = function (string $v): string {
            return substr($v, 1);
        };

        $repositoriesNode
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->prototype('array')
            ->children()
                ->scalarNode('repository_class')->end()
                ->scalarNode('aggregate_type')->isRequired()->end()
                ->scalarNode('aggregate_translator')->isRequired()
                    ->beforeNormalization()
                        ->ifTrue($beginsWithAt)
                        ->then($removeFirstCharacter)
                    ->end()
                ->end()
                ->scalarNode('stream_name')->defaultValue(null)->end()
                ->scalarNode('store')->defaultValue('default')->end()
                ->booleanNode('one_stream_per_aggregate')->defaultValue(false)->end()
                ->booleanNode('disable_identity_map')->defaultValue(false)->end()
            ->end();

        $node
            ->fixXmlConfig('repository', 'repositories')
            ->children()
                ->append($repositoriesNode)
            ->end();
    }
}
