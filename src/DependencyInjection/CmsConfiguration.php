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
                                ->info('The template name that will be shown to the user.')
                            ->end()
                            ->booleanNode('default')
                                ->defaultFalse()
                                ->info('This is the default template. If no template is set, this one will be used.')
                            ->end()
                            ->arrayNode('items')
                                ->requiresAtLeastOneElement()
                                ->arrayPrototype()
                                    ->ignoreExtraKeys()
                                    ->children()
                                        ->scalarNode('type')
                                            ->isRequired()
                                            ->info('The item type. Built in options are: text, html')
                                        ->end()
                                        ->scalarNode('required')->defaultFalse()->end()
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
