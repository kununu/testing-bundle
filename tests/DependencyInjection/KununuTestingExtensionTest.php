<?php declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\DependencyInjection;

use Kununu\TestingBundle\DependencyInjection\KununuTestingExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

final class KununuTestingExtensionTest extends AbstractExtensionTestCase
{
    public function testThatWhenConnectionsAreConfiguredThenParametersWithConnectionConfigsAreSet()
    {
        $this->load([
            'connections' => [
                'default' => [
                    'excluded_tables' => ['table1', 'table2']
                ],
                'monolithic' => [
                    'excluded_tables' => ['table1', 'table3']
                ],
                'other_connection' => [
                    'excluded_tables' => []
                ]
            ]
        ]);

        $this->assertContainerBuilderHasParameter(
            'kununu_testing.connections.default',
            ['excluded_tables' => ['table1', 'table2']]
        );

        $this->assertContainerBuilderHasParameter(
            'kununu_testing.connections.monolithic',
            ['excluded_tables' => ['table1', 'table3']]
        );

        $this->assertContainerBuilderHasParameter(
            'kununu_testing.connections.other_connection',
            ['excluded_tables' => []]
        );
    }

    public function testThatWhenElasticSearchIsConfiguredThenParametersWithConfigsAreSet()
    {
        $this->load([
            'elastic_search' => [
                'alias_1' => [
                    'index_name' => 'index_1',
                    'service' => 'service_1'
                ],
                'alias_2' => [
                    'index_name' => 'index_2',
                    'service' => 'service_1'
                ],
                'alias_3' => [
                    'index_name' => 'index_1',
                    'service' => 'service_2'
                ],
            ]
        ]);

        $this->assertContainerBuilderHasParameter(
            'kununu_testing.elastic_search',
            [
                'alias_1' => [
                    'index_name' => 'index_1',
                    'service' => 'service_1'
                ],
                'alias_2' => [
                    'index_name' => 'index_2',
                    'service' => 'service_1'
                ],
                'alias_3' => [
                    'index_name' => 'index_1',
                    'service' => 'service_2'
                ],
            ]
        );
    }

    protected function getContainerExtensions(): array
    {
        return [
            new KununuTestingExtension()
        ];
    }
}
