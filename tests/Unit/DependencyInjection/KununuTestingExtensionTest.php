<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Unit\DependencyInjection;

use Kununu\TestingBundle\DependencyInjection\KununuTestingExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

final class KununuTestingExtensionTest extends AbstractExtensionTestCase
{
    public function testThatWhenConnectionsAreConfiguredThenParametersWithConnectionConfigsAreSet(): void
    {
        $this->load([
            'connections' => [
                'default' => [
                    'excluded_tables' => ['table1', 'table2'],
                ],
                'persistence' => [
                    'load_command_fixtures_classes_namespace' => ['App\DataFixtures\Fixture1'],
                ],
                'monolithic' => [
                    'excluded_tables'                         => ['table1', 'table3'],
                    'load_command_fixtures_classes_namespace' => ['App\DataFixtures\Fixture2'],
                ],
                'other_connection' => [
                    'excluded_tables' => [],
                ],
                'empty_config_connection' => [],
            ],
        ]);

        $this->assertContainerBuilderHasParameter(
            'kununu_testing.connections.default',
            ['excluded_tables' => ['table1', 'table2'], 'load_command_fixtures_classes_namespace' => []]
        );

        $this->assertContainerBuilderHasParameter(
            'kununu_testing.connections.persistence',
            ['excluded_tables' => [], 'load_command_fixtures_classes_namespace' => ['App\DataFixtures\Fixture1']]
        );

        $this->assertContainerBuilderHasParameter(
            'kununu_testing.connections.monolithic',
            [
                'excluded_tables'                         => ['table1', 'table3'],
                'load_command_fixtures_classes_namespace' => ['App\DataFixtures\Fixture2'],
            ]
        );

        $this->assertContainerBuilderHasParameter(
            'kununu_testing.connections.other_connection',
            ['excluded_tables' => [], 'load_command_fixtures_classes_namespace' => []]
        );

        $this->assertContainerBuilderHasParameter(
            'kununu_testing.connections.empty_config_connection',
            ['excluded_tables' => [], 'load_command_fixtures_classes_namespace' => []]
        );
    }

    public function testThatWhenSearchEnginesAreConfiguredThenParametersWithConfigsAreSet(): void
    {
        $this->load([
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
                    'index_name' => 'index_1',
                    'service'    => 'service_2',
                ],
            ],
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
                    'index_name' => 'index_1',
                    'service'    => 'service_2',
                ],
            ],
        ]);

        $this->assertContainerBuilderHasParameter(
            'kununu_testing.elastic_search',
            [
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
                    'index_name' => 'index_1',
                    'service'    => 'service_2',
                ],
            ]
        );

        $this->assertContainerBuilderHasParameter(
            'kununu_testing.open_search',
            [
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
                    'index_name' => 'index_1',
                    'service'    => 'service_2',
                ],
            ]
        );
    }

    protected function getContainerExtensions(): array
    {
        return [
            new KununuTestingExtension(),
        ];
    }
}
