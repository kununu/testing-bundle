<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Unit\DependencyInjection;

final class DynamoDbConfigurationTest extends ConfigurationTestCase
{
    public static function validProcessedConfigurationDataProvider(): array
    {
        return [
            'no_configuration'                                                  => [
                [
                    [],
                ],
                [
                    'dynamo_db' => [],
                ],
            ],
            'dynamo_db_with_minimal_configuration'                             => [
                [
                    [
                        'dynamo_db' => [
                            'default' => [
                                'service' => 'aws.dynamo_db.client.default',
                            ],
                        ],
                    ],
                ],
                [
                    'dynamo_db' => [
                        'default' => [
                            'load_command_fixtures_classes_namespace' => [],
                            'service'                                 => 'aws.dynamo_db.client.default',
                            'table_names'                             => [],
                        ],
                    ],
                ],
            ],
            'dynamo_db_with_table_names'                                       => [
                [
                    [
                        'dynamo_db' => [
                            'default' => [
                                'service'     => 'aws.dynamo_db.client.default',
                                'table_names' => ['table1', 'table2'],
                            ],
                        ],
                    ],
                ],
                [
                    'dynamo_db' => [
                        'default' => [
                            'load_command_fixtures_classes_namespace' => [],
                            'service'                                 => 'aws.dynamo_db.client.default',
                            'table_names'                             => ['table1', 'table2'],
                        ],
                    ],
                ],
            ],
            'dynamo_db_with_load_command_fixtures_classes_namespace'           => [
                [
                    [
                        'dynamo_db' => [
                            'default' => [
                                'service'                                 => 'aws.dynamo_db.client.default',
                                'load_command_fixtures_classes_namespace' => [
                                    'App\DataFixtures\DynamoDb\Fixture1',
                                    'App\DataFixtures\DynamoDb\Fixture2',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'dynamo_db' => [
                        'default' => [
                            'load_command_fixtures_classes_namespace' => [
                                'App\DataFixtures\DynamoDb\Fixture1',
                                'App\DataFixtures\DynamoDb\Fixture2',
                            ],
                            'service'                                 => 'aws.dynamo_db.client.default',
                            'table_names'                             => [],
                        ],
                    ],
                ],
            ],
            'dynamo_db_with_full_configuration'                                => [
                [
                    [
                        'dynamo_db' => [
                            'default' => [
                                'service'                                 => 'aws.dynamo_db.client.default',
                                'table_names'                             => ['table1', 'table2'],
                                'load_command_fixtures_classes_namespace' => [
                                    'App\DataFixtures\DynamoDb\Fixture1',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'dynamo_db' => [
                        'default' => [
                            'load_command_fixtures_classes_namespace' => [
                                'App\DataFixtures\DynamoDb\Fixture1',
                            ],
                            'service'                                 => 'aws.dynamo_db.client.default',
                            'table_names'                             => ['table1', 'table2'],
                        ],
                    ],
                ],
            ],
            'multiple_dynamo_db_services_with_different_configurations'        => [
                [
                    [
                        'dynamo_db' => [
                            'default'   => [
                                'service'                                 => 'aws.dynamo_db.client.default',
                                'table_names'                             => ['table1', 'table2'],
                                'load_command_fixtures_classes_namespace' => [
                                    'App\DataFixtures\DynamoDb\DefaultFixture1',
                                    'App\DataFixtures\DynamoDb\DefaultFixture2',
                                ],
                            ],
                            'secondary' => [
                                'service'     => 'aws.dynamo_db.client.secondary',
                                'table_names' => ['table3'],
                            ],
                            'tertiary'  => [
                                'service'                                 => 'aws.dynamo_db.client.tertiary',
                                'load_command_fixtures_classes_namespace' => [
                                    'App\DataFixtures\DynamoDb\TertiaryFixture',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'dynamo_db' => [
                        'default'   => [
                            'load_command_fixtures_classes_namespace' => [
                                'App\DataFixtures\DynamoDb\DefaultFixture1',
                                'App\DataFixtures\DynamoDb\DefaultFixture2',
                            ],
                            'service'                                 => 'aws.dynamo_db.client.default',
                            'table_names'                             => ['table1', 'table2'],
                        ],
                        'secondary' => [
                            'load_command_fixtures_classes_namespace' => [],
                            'service'                                 => 'aws.dynamo_db.client.secondary',
                            'table_names'                             => ['table3'],
                        ],
                        'tertiary'  => [
                            'load_command_fixtures_classes_namespace' => [
                                'App\DataFixtures\DynamoDb\TertiaryFixture',
                            ],
                            'service'                                 => 'aws.dynamo_db.client.tertiary',
                            'table_names'                             => [],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected static function getInvalidProcessedConfigurationData(): array
    {
        return [
            'dynamo_db_as_null'                                    => [
                [
                    [
                        'dynamo_db' => null,
                    ],
                ],
            ],
            'dynamo_db_as_empty_array'                             => [
                [
                    [
                        'dynamo_db' => [],
                    ],
                ],
            ],
            'dynamo_db_service_without_service_key'                => [
                [
                    [
                        'dynamo_db' => [
                            'default' => [
                                'table_names' => ['table1'],
                            ],
                        ],
                    ],
                ],
            ],
            'dynamo_db_service_with_empty_service'                 => [
                [
                    [
                        'dynamo_db' => [
                            'default' => [
                                'service' => '',
                            ],
                        ],
                    ],
                ],
            ],
            'dynamo_db_service_with_null_service'                  => [
                [
                    [
                        'dynamo_db' => [
                            'default' => [
                                'service' => null,
                            ],
                        ],
                    ],
                ],
            ],
            'dynamo_db_load_command_fixtures_classes_namespace_as_string' => [
                [
                    [
                        'dynamo_db' => [
                            'default' => [
                                'service'                                 => 'aws.dynamo_db.client.default',
                                'load_command_fixtures_classes_namespace' => 'App\DataFixtures\DynamoDb\Fixture1',
                            ],
                        ],
                    ],
                ],
            ],
            'dynamo_db_table_names_as_string'                      => [
                [
                    [
                        'dynamo_db' => [
                            'default' => [
                                'service'     => 'aws.dynamo_db.client.default',
                                'table_names' => 'table1',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function getNodeName(): string
    {
        return 'dynamo_db';
    }
}
