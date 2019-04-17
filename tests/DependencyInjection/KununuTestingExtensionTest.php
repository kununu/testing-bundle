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

    protected function getContainerExtensions(): array
    {
        return [
            new KununuTestingExtension()
        ];
    }
}
