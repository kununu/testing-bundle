<?php declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\DependencyInjection;

use Kununu\TestingBundle\DependencyInjection\Configuration;
use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;

final class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    public function testEmptyConfiguration(): void
    {
        $this->assertProcessedConfigurationEquals(
            [],
            ['connections' => [], 'elastic_search' => []]
        );
    }

    /**
     * @dataProvider processedConfigurationForConnectionsNodeDataProvider
     *
     * @param array $configurationValues
     * @param array $expectedProcessedConfiguration
     */
    public function testProcessedConfigurationForConnectionsNode(array $configurationValues, array $expectedProcessedConfiguration): void
    {
        $this->assertProcessedConfigurationEquals(
            $configurationValues,
            $expectedProcessedConfiguration,
            'connections'
        );
    }

    public function processedConfigurationForConnectionsNodeDataProvider()
    {
        return [
            'no_configuration' => [
                [
                    [],
                ],
                [
                    'connections' => [],
                ],
            ],
            'connection_with_empty_configuration' => [
                [
                    [
                        'connections' => [
                            'default' => [],
                        ],
                    ],
                ],
                [
                    'connections' => [
                        'default' => [
                            'load_command_fixtures_classes_namespace' => [],
                            'excluded_tables'                         => [],
                        ],
                    ],
                ],
            ],
            'connection_with_excluded_tables' => [
                [
                    [
                        'connections' => [
                            'default' => [
                                'excluded_tables' => ['table1', 'table2'],
                            ],
                        ],
                    ],
                ],
                [
                    'connections' => [
                        'default' => [
                            'load_command_fixtures_classes_namespace' => [],
                            'excluded_tables'                         => ['table1', 'table2'],
                        ],
                    ],
                ],
            ],
            'connection_with_load_command_fixtures_classes_namespace' => [
                [
                    [
                        'connections' => [
                            'default' => [
                                'load_command_fixtures_classes_namespace' => [
                                    'App/DataFixtures/Fixture1',
                                    'App/DataFixtures/Fixture2'
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'connections' => [
                        'default' => [
                            'load_command_fixtures_classes_namespace' => [
                                'App/DataFixtures/Fixture1',
                                'App/DataFixtures/Fixture2'
                            ],
                            'excluded_tables'                         => [],
                        ],
                    ],
                ],
            ],
            'multiple_connections_configuration_settings_empty' => [
                [
                    [
                        'connections' => [
                            'default'    => [],
                            'monolithic' => [],
                        ],
                    ],
                ],
                [
                    'connections' => [
                        'default' => [
                            'load_command_fixtures_classes_namespace' => [],
                            'excluded_tables'                         => [],
                        ],
                        'monolithic' => [
                            'load_command_fixtures_classes_namespace' => [],
                            'excluded_tables'                         => [],
                        ],
                    ],
                ],
            ],
            'multiple_connections_with_config' => [
                [
                    [
                        'connections' => [
                            'default' => [
                                'load_command_fixtures_classes_namespace' => [
                                    'App/DataFixtures/Fixture1',
                                    'App/DataFixtures/Fixture2'
                                ],
                                'excluded_tables' => ['table1', 'table2'],
                            ],
                            'monolithic' => [
                                'load_command_fixtures_classes_namespace' => [
                                    'App/DataFixtures/Fixture4',
                                ],
                                'excluded_tables' => ['table1', 'table3', 'table4'],
                            ],
                        ],
                    ],
                ],
                [
                    'connections' => [
                        'default' => [
                            'load_command_fixtures_classes_namespace' => [
                                'App/DataFixtures/Fixture1',
                                'App/DataFixtures/Fixture2'
                            ],
                            'excluded_tables'                         => ['table1', 'table2'],
                        ],
                        'monolithic' => [
                            'load_command_fixtures_classes_namespace' => [
                                'App/DataFixtures/Fixture4',
                            ],
                            'excluded_tables'                         => ['table1', 'table3', 'table4'],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider connectionsNodeIsInvalidIfAtLeastOneConnectionIsNotProvidedDataProvider
     *
     * @param array $configurationValues
     */
    public function testConnectionsNodeIsInvalidIfAtLeastOneConnectionIsNotProvided(array $configurationValues): void
    {
        $this->assertConfigurationIsInvalid(
            $configurationValues,
            'kununu_testing.connections'
        );
    }

    public function connectionsNodeIsInvalidIfAtLeastOneConnectionIsNotProvidedDataProvider()
    {
        return [
            'connections_as_null' => [
                [
                    [
                        'connections' => null,
                    ],
                ],
            ],
            'connections_as_empty_array' => [
                [
                    [
                        'connections' => [],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider processedConfigurationForElasticSearchNodeDataProvider
     *
     * @param array $configurationValues
     * @param array $expectedProcessedConfiguration
     */
    public function testProcessedConfigurationForElasticSearchNode(array $configurationValues, array $expectedProcessedConfiguration): void
    {
        $this->assertProcessedConfigurationEquals(
            $configurationValues,
            $expectedProcessedConfiguration,
            'elastic_search'
        );
    }

    public function processedConfigurationForElasticSearchNodeDataProvider()
    {
        return [
            'no_configuration' => [
                [
                    [],
                ],
                [
                    'elastic_search' => [],
                ],
            ],
            'with_configuration' => [
                [
                    [
                        'elastic_search' => [
                            'alias_1' => [
                                'index_name' => 'index_1',
                                'service'    => 'service_1',
                            ],
                            'alias_2' => [
                                'index_name' => 'index_2',
                                'service'    => 'service_1',
                            ],
                            'alias_3' => [
                                'index_name' => 'index_1',
                                'service'    => 'service_2',
                            ],
                        ],
                    ],
                ],
                [
                    'elastic_search' => [
                        'alias_1' => [
                            'index_name' => 'index_1',
                            'service'    => 'service_1',
                        ],
                        'alias_2' => [
                            'index_name' => 'index_2',
                            'service'    => 'service_1',
                        ],
                        'alias_3' => [
                            'index_name' => 'index_1',
                            'service'    => 'service_2',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider elasticSearchNodeIsInvalidIfAtLeastOneItemIsNotProvidedDataProvider
     *
     * @param array $configurationValues
     */
    public function testElasticSearchNodeIsInvalidIfAtLeastOneItemIsNotProvided(array $configurationValues): void
    {
        $this->assertConfigurationIsInvalid(
            $configurationValues,
            'kununu_testing.elastic_search'
        );
    }

    public function elasticSearchNodeIsInvalidIfAtLeastOneItemIsNotProvidedDataProvider()
    {
        return [
            'elastic_search_as_null' => [
                [
                    [
                        'elastic_search' => null,
                    ],
                ],
            ],
            'elastic_search_as_empty_array' => [
                [
                    [
                        'elastic_search' => [],
                    ],
                ],
            ],
        ];
    }

    protected function getConfiguration()
    {
        return new Configuration();
    }
}
