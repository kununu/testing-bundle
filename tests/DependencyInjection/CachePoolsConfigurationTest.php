<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\DependencyInjection;

final class CachePoolsConfigurationTest extends ConfigurationTestCase
{
    public function validProcessedConfigurationDataProvider(): array
    {
        return [
            'no_configuration'   => [
                [
                    [],
                ],
                [
                    'cache' => [
                        'enable' => true,
                        'pools'  => [],
                    ],
                ],
            ],
            'with_configuration' => [
                [
                    [
                        'cache' => [
                            'pools' => [
                                'app.cache.first'  => [
                                    'load_command_fixtures_classes_namespace' => [
                                        'App/DataFixtures/Fixture1',
                                        'App/DataFixtures/Fixture2',
                                    ],
                                ],
                                'app.cache.second' => [
                                    'load_command_fixtures_classes_namespace' => [
                                        'App/DataFixtures/Fixture3',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'cache' => [
                        'enable' => true,
                        'pools'  => [
                            'app.cache.first'  => [
                                'load_command_fixtures_classes_namespace' => [
                                    'App/DataFixtures/Fixture1',
                                    'App/DataFixtures/Fixture2',
                                ],
                            ],
                            'app.cache.second' => [
                                'load_command_fixtures_classes_namespace' => [
                                    'App/DataFixtures/Fixture3',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function getNodeName(): string
    {
        return 'cache';
    }
}
