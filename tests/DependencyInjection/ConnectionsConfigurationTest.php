<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\DependencyInjection;

final class ConnectionsConfigurationTest extends ConfigurationTestCase
{
    public function validProcessedConfigurationDataProvider(): array
    {
        return [
            'no_configuration'                                        => [
                [
                    [],
                ],
                [
                    'connections' => [],
                ],
            ],
            'connection_with_empty_configuration'                     => [
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
            'connection_with_excluded_tables'                         => [
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
                                    'App/DataFixtures/Fixture2',
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
                        'connections' => [
                            'default'    => [],
                            'monolithic' => [],
                        ],
                    ],
                ],
                [
                    'connections' => [
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
                        'connections' => [
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
                    'connections' => [
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

    protected function getNodeName(): string
    {
        return 'connections';
    }
}
