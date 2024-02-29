<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('kununu_testing');

        $this
            ->addConnectionsSection($rootNode = $treeBuilder->getRootNode()->fixXmlConfig('connection'), 'connections')
            ->addConnectionsSection($rootNode, 'non_transactional_connections')
            ->addElasticsearchSection($rootNode)
            ->addCacheSection($rootNode)
            ->addHttpClientSection($rootNode);

        return $treeBuilder;
    }

    private function addConnectionsSection(ArrayNodeDefinition $node, string $nodeName): self
    {
        $node
            ->children()
                ->arrayNode($nodeName)
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
            ->end();

        return $this;
    }

    private function addElasticsearchSection(ArrayNodeDefinition $node): self
    {
        $node
            ->children()
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
            ->end();

        return $this;
    }

    private function addCacheSection(ArrayNodeDefinition $node): self
    {
        $node
            ->children()
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
            ->end();

        return $this;
    }

    private function addHttpClientSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
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
            ->end();
    }
}
