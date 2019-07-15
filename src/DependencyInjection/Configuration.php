<?php

namespace FixturesDocumentation\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This class validates and merges configuration from the app/config files.
 * @codeCoverageIgnore
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('fixtures_documentation');
        $rootNode
            ->children()
                ->scalarNode('title')
                    ->defaultValue('Fixtures documentation')->end()
                ->arrayNode('reloadCommands')
                    ->scalarPrototype()->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
