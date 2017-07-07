<?php

namespace Sylake\SyliusConsumerPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sylake_sylius_consumer');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('denormalizer')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('product')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('name_attribute')->defaultValue('name')->end()
                                ->scalarNode('description_attribute')->defaultValue('description')->end()
                                ->scalarNode('price_attribute')->defaultValue('price')->end()
                                ->scalarNode('image_attribute')->defaultValue('images')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
