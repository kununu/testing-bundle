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
                ->arrayNode('ssl_check_disable')
                    ->children()
                        ->booleanNode('enable')
                            ->info('Enable disabling SSL checks for requests made with Http clients on selected hosts')
                            ->defaultFalse()
                        ->end()
                        ->arrayNode('clients')
                            ->info('Http client services ids. Currently only Guzzle supported')
                            ->scalarPrototype()
                            ->end()
                        ->end()
                        ->arrayNode('domains')
                            ->info('Domains for which to disable SSL checks')
                            ->scalarPrototype()
                            ->end()
                        ->end()
                        ->scalarNode('env_var')
                            ->info('Environment variable that has the hostname')
                            ->cannotBeEmpty()
                            ->defaultValue('VIRTUAL_HOST')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('connections')
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->arrayNode('load_command_fixtures_classes_namespace')
                                ->scalarPrototype()
                                ->end()
                            ->end()
                            ->arrayNode('excluded_tables')
                                ->scalarPrototype()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('elastic_search')
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
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
            ->end()
        ;

        return $treeBuilder;
    }
}
