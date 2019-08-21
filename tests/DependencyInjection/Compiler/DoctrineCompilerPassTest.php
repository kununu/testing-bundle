<?php declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\DependencyInjection\Compiler;

use Kununu\DataFixtures\Executor\ConnectionExecutor;
use Kununu\DataFixtures\Loader\ConnectionFixturesLoader;
use Kununu\DataFixtures\Purger\ConnectionPurger;
use Kununu\TestingBundle\DependencyInjection\Compiler\DoctrineCompilerPass;
use Kununu\TestingBundle\Service\Orchestrator;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class DoctrineCompilerPassTest extends AbstractCompilerPassTestCase
{
    public function testThatCreatesOrchestratorForEachDoctrineConnection(): void
    {
        $connections = [
            'default'    => 'doctrine.default_connection',
            'monolithic' => 'doctrine.monolithic_connection',
        ];

        $tables = [
            'default'    => ['table1', 'table2'],
            'monolithic' => ['table1', 'table3'],
        ];

        $this->setParameter('doctrine.connections', $connections);

        $this->setParameter(
            'kununu_testing.connections.default',
            [
                'excluded_tables' => $tables['default'],
            ]
        );

        $this->setParameter(
            'kununu_testing.connections.monolithic',
            [
                'excluded_tables' => $tables['monolithic'],
            ]
        );
        $this->compile();

        foreach ($connections as $connName => $connId) {
            $purgerId = sprintf('kununu_testing.orchestrator.connections.%s.purger', $connName);
            $executorId = sprintf('kununu_testing.orchestrator.connections.%s.executor', $connName);
            $loaderId = sprintf('kununu_testing.orchestrator.connections.%s.loader', $connName);
            $orchestratorId = sprintf('kununu_testing.orchestrator.connections.%s', $connName);

            $this->assertContainerBuilderHasServiceDefinitionWithArgument(
                $purgerId,
                0,
                new Reference($connId)
            );
            $this->assertContainerBuilderHasServiceDefinitionWithArgument(
                $purgerId,
                1,
                $tables[$connName]
            );
            $this->assertContainerBuilderHasService(
                $purgerId,
                ConnectionPurger::class
            );
            $this->assertTrue($this->container->getDefinition($purgerId)->isPrivate());

            $this->assertContainerBuilderHasServiceDefinitionWithArgument(
                $executorId,
                0,
                new Reference($connId)
            );
            $this->assertContainerBuilderHasServiceDefinitionWithArgument(
                $executorId,
                1,
                new Reference($purgerId)
            );
            $this->assertContainerBuilderHasService(
                $executorId,
                ConnectionExecutor::class
            );
            $this->assertTrue($this->container->getDefinition($executorId)->isPrivate());

            $this->assertContainerBuilderHasService(
                $loaderId,
                ConnectionFixturesLoader::class
            );
            $this->assertTrue($this->container->getDefinition($loaderId)->isPrivate());

            $this->assertContainerBuilderHasServiceDefinitionWithArgument(
                $orchestratorId,
                0,
                new Reference($executorId)
            );
            $this->assertContainerBuilderHasServiceDefinitionWithArgument(
                $orchestratorId,
                1,
                new Reference($purgerId)
            );
            $this->assertContainerBuilderHasServiceDefinitionWithArgument(
                $orchestratorId,
                2,
                new Reference($loaderId)
            );
            $this->assertContainerBuilderHasService(
                $orchestratorId,
                Orchestrator::class
            );
            $this->assertTrue($this->container->getDefinition($orchestratorId)->isPublic());
        }
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new DoctrineCompilerPass());
    }
}
