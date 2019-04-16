<?php declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\DependencyInjection;

use Kununu\TestingBundle\DependencyInjection\Configuration;
use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;

final class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    /**
     * @dataProvider processedConfigurationForConnectionsNodeDataProvider
     *
     * @param array $configurationValues
     * @param array $expectedProcessedConfiguration
     */
    public function testProcessedConfigurationForConnectionsNode(array $configurationValues, array $expectedProcessedConfiguration)
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
                    []
                ],
                [
                    'connections' => []
                ]
            ],
            'connection_without_excluded_tables' => [
                [
                    [
                        'connections' => [
                            'default' => []
                        ]
                    ]
                ],
                [
                    'connections' => [
                        'default' => [
                            'excluded_tables' => []
                        ]
                    ]
                ]
            ],
            'connection_with_excluded_tables' => [
                [
                    [
                        'connections' => [
                            'default' => [
                                'excluded_tables' => ['table1', 'table2']
                            ]
                        ]
                    ]
                ],
                [
                    'connections' => [
                        'default' => [
                            'excluded_tables' => ['table1', 'table2']
                        ]
                    ]
                ]
            ],
            'multiple_connections_without_excluded_tables' => [
                [
                    [
                        'connections' => [
                            'default' => [],
                            'monolithic' => []
                        ]
                    ]
                ],
                [
                    'connections' => [
                        'default' => [
                            'excluded_tables' => []
                        ],
                        'monolithic' => [
                            'excluded_tables' => []
                        ],
                    ],
                ],
            ],
            'multiple_connections_with_excluded_tables' => [
                [
                    [
                        'connections' => [
                            'default' => [
                                'excluded_tables' => ['table1', 'table2']
                            ],
                            'monolithic' => [
                                'excluded_tables' => ['table1', 'table3', 'table4']
                            ],
                        ],
                    ],
                ],
                [
                    'connections' => [
                        'default' => [
                            'excluded_tables' => ['table1', 'table2'],
                        ],
                        'monolithic' => [
                            'excluded_tables' => ['table1', 'table3', 'table4']
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider connectionsNodeIsInvalidIfAtLeastOneConnectionsIsNotProvidedDataProvider
     *
     * @param array $configurationValues
     */
    public function testConnectionsNodeIsInvalidIfAtLeastOneConnectionsIsNotProvided(array $configurationValues)
    {
        $this->assertConfigurationIsInvalid(
            $configurationValues,
            'kununu_testing.connections'
        );
    }

    public function connectionsNodeIsInvalidIfAtLeastOneConnectionsIsNotProvidedDataProvider()
    {
        return [
            'connections_as_null' => [
                [
                    [
                        'connections' => null
                    ],
                ]
            ],
            'connections_as_empty_array' => [
                [
                    [
                        'connections' => []
                    ],
                ]
            ]
        ];
    }

    protected function getConfiguration()
    {
        return new Configuration();
    }
}
