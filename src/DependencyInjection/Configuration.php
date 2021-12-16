<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('kununu_testing');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->fixXmlConfig('connection')
            ->children()
                ->arrayNode('connections')
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->arrayNode('load_command_fixtures_classes_namespace')
                                ->scalarPrototype()
                                ->end()
                                ->defaultValue([])
                            ->end()
                            ->arrayNode('excluded_tables')
                                ->scalarPrototype()
                                ->end()
                                ->defaultValue([])
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('elastic_search')
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->arrayNode('load_command_fixtures_classes_namespace')
                                ->scalarPrototype()
                                ->end()
                                ->defaultValue([])
                            ->end()
                            ->scalarNode('service')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('index_name')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('cache')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enable')
                            ->info('Enable creating orchestrators for cache pools')
                            ->defaultTrue()
                        ->end()
                        ->arrayNode('pools')
                            ->useAttributeAsKey('name')
                            ->defaultValue([])
                            ->arrayPrototype()
                                ->children()
                                    ->arrayNode('load_command_fixtures_classes_namespace')
                                        ->scalarPrototype()
                                        ->end()
                                        ->defaultValue([])
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('http_client')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('clients')
                            ->defaultValue([])
                            ->scalarPrototype()
                                ->cannotBeEmpty()
                                ->info('Symfony service id of the http client')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
