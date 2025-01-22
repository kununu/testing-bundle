<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Unit\DependencyInjection;

final class OpenSearchConfigurationTest extends ConfigurationTestCase
{
    public static function validProcessedConfigurationDataProvider(): array
    {
        return [
            'no_configuration'   => [
                [
                    [],
                ],
                [
                    'open_search' => [],
                ],
            ],
            'with_configuration' => [
                [
                    [
                        'open_search' => [
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
                    'open_search' => [
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
            'open_search_as_null'        => [
                [
                    [
                        'open_search' => null,
                    ],
                ],
            ],
            'open_search_as_empty_array' => [
                [
                    [
                        'open_search' => [],
                    ],
                ],
            ],
        ];
    }

    protected function getNodeName(): string
    {
        return 'open_search';
    }
}
