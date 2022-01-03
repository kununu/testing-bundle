<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\DependencyInjection;

final class NonTransactionalConnectionsConfigurationTest extends ConfigurationTestCase
{
    public function validProcessedConfigurationDataProvider(): array
    {
        return [
            'no_configuration'                                        => [
                [
                    [],
                ],
                [
                    'non_transactional_connections' => [],
                ],
            ],
            'connection_with_empty_configuration'                     => [
                [
                    [
                        'non_transactional_connections' => [
                            'default' => [],
                        ],
                    ],
                ],
                [
                    'non_transactional_connections' => [
                        'default' => [
                            'load_command_fixtures_classes_namespace' => [],
                            'excluded_tables'                         => [],
                        ],
                    ],
                ],
            ],
            'connection_with_excluded_tables'                         => [
                [
                    [
                        'non_transactional_connections' => [
                            'default' => [
                                'excluded_tables' => ['table1', 'table2'],
                            ],
                        ],
                    ],
                ],
                [
                    'non_transactional_connections' => [
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
                        'non_transactional_connections' => [
                            'default' => [
                                'load_command_fixtures_classes_namespace' => [
                                    'App/DataFixtures/Fixture1',
                                    'App/DataFixtures/Fixture2',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'non_transactional_connections' => [
                        'default' => [
                            'load_command_fixtures_classes_namespace' => [
                                'App/DataFixtures/Fixture1',
                                'App/DataFixtures/Fixture2',
                            ],
                            'excluded_tables'                         => [],
                        ],
                    ],
                ],
            ],
            'multiple_connections_configuration_settings_empty'       => [
                [
                    [
                        'non_transactional_connections' => [
                            'default'    => [],
                            'monolithic' => [],
                        ],
                    ],
                ],
                [
                    'non_transactional_connections' => [
                        'default'    => [
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
            'multiple_connections_with_config'                        => [
                [
                    [
                        'non_transactional_connections' => [
                            'default'    => [
                                'load_command_fixtures_classes_namespace' => [
                                    'App/DataFixtures/Fixture1',
                                    'App/DataFixtures/Fixture2',
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
                [
                    'non_transactional_connections' => [
                        'default'    => [
                            'load_command_fixtures_classes_namespace' => [
                                'App/DataFixtures/Fixture1',
                                'App/DataFixtures/Fixture2',
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

    protected function getInvalidProcessedConfigurationData(): array
    {
        return [
            'connections_as_null'        => [
                [
                    [
                        'non_transactional_connections' => null,
                    ],
                ],
            ],
            'connections_as_empty_array' => [
                [
                    [
                        'non_transactional_connections' => [],
                    ],
                ],
            ],
        ];
    }

    protected function getNodeName(): string
    {
        return 'non_transactional_connections';
    }
}
