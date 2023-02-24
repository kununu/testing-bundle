<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\DependencyInjection;

final class ElasticsearchConfigurationTest extends ConfigurationTestCase
{
    public static function validProcessedConfigurationDataProvider(): array
    {
        return [
            'no_configuration'   => [
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
                                'load_command_fixtures_classes_namespace' => [],
                                'index_name'                              => 'index_2',
                                'service'                                 => 'service_1',
                            ],
                            'alias_3' => [
                                'load_command_fixtures_classes_namespace' => [
                                    'App/DataFixtures/Fixture1',
                                    'App/DataFixtures/Fixture2',
                                ],
                                'index_name'                              => 'index_1',
                                'service'                                 => 'service_2',
                            ],
                        ],
                    ],
                ],
                [
                    'elastic_search' => [
                        'alias_1' => [
                            'load_command_fixtures_classes_namespace' => [],
                            'index_name'                              => 'index_1',
                            'service'                                 => 'service_1',
                        ],
                        'alias_2' => [
                            'load_command_fixtures_classes_namespace' => [],
                            'index_name'                              => 'index_2',
                            'service'                                 => 'service_1',
                        ],
                        'alias_3' => [
                            'load_command_fixtures_classes_namespace' => [
                                'App/DataFixtures/Fixture1',
                                'App/DataFixtures/Fixture2',
                            ],
                            'index_name'                              => 'index_1',
                            'service'                                 => 'service_2',
                        ],
                    ],
                ],
            ],
        ];
    }

    protected static function getInvalidProcessedConfigurationData(): array
    {
        return [
            'elastic_search_as_null'        => [
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

    protected function getNodeName(): string
    {
        return 'elastic_search';
    }
}
