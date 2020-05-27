<?php

declare(strict_types=1);

namespace App\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class CmsConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $tb = new TreeBuilder('cms');
        $rootNode = $tb->getRootNode();

        $this->addTemplatesSection($rootNode);

        return $tb;
    }

    private function addTemplatesSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->arrayNode('templates')
                    ->requiresAtLeastOneElement()
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->info('The template name that will be shown to the user.')
                            ->end()
                            ->booleanNode('edit_meta_description')
                                ->defaultTrue()
                                ->info('If false, the meta description won\'t be editable for the page.')
                            ->end()
                            ->arrayNode('items')
                                ->requiresAtLeastOneElement()
                                ->arrayPrototype()
                                    ->ignoreExtraKeys()
                                    ->children()
                                        ->scalarNode('type')
                                            ->isRequired()
                                            ->cannotBeEmpty()
                                            ->info('The item type. Built in options are: text, html')
                                        ->end()
                                        ->scalarNode('name')
                                            ->isRequired()
                                            ->cannotBeEmpty()
                                            ->info('The name of the item to be shown to the user.')
                                        ->end()
                                        ->scalarNode('required')->defaultFalse()->end()
                                        ->scalarNode('help')->end()
                                        ->variableNode('params')->defaultValue([])->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
