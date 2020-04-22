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
    public function testThatCreatesOrchestratorAndLoadDatabaseFixturesCommand(): void
    {
        $connections = [
            'default'     => 'doctrine.default_connection',
            'monolithic'  => 'doctrine.monolithic_connection',
        ];

        $tables = [
            'default'     => [],
            'monolithic'  => ['table1', 'table3'],
        ];

        $loadCommandFixturesClassesNamespace = [
            'default'     => ['App\DataFixtures\Fixture1'],
            'monolithic'  => ['App\DataFixtures\Fixture2', 'App\DataFixtures\Fixture3'],
        ];

        $this->setParameter('doctrine.connections', $connections);

        $this->setParameter(
            'kununu_testing.connections.default',
            [
                'load_command_fixtures_classes_namespace' => $loadCommandFixturesClassesNamespace['default'],
                'excluded_tables'                         => $tables['default'],
            ]
        );
        $this->setParameter(
            'kununu_testing.connections.monolithic',
            [
                'load_command_fixtures_classes_namespace' => $loadCommandFixturesClassesNamespace['monolithic'],
                'excluded_tables'                         => $tables['monolithic'],
            ]
        );
        $this->compile();

        foreach ($connections as $connName => $connId) {
            $orchestratorId = $this->assertOrchestratorServiceExists($connName, $connId, $tables[$connName]);

            $loadDatabaseFixturesCommandId = sprintf('kununu_testing.command.load_fixtures.connections.%s', $connName);
            $this->assertContainerBuilderHasService($loadDatabaseFixturesCommandId);
            $this->assertContainerBuilderHasServiceDefinitionWithArgument(
                $loadDatabaseFixturesCommandId,
                0,
                $connName
            );
            $this->assertContainerBuilderHasServiceDefinitionWithArgument(
                $loadDatabaseFixturesCommandId,
                1,
                new Reference($orchestratorId)
            );
            $this->assertContainerBuilderHasServiceDefinitionWithArgument(
                $loadDatabaseFixturesCommandId,
                2,
                $loadCommandFixturesClassesNamespace[$connName]
            );
        }
    }

    public function testThatCreatesOrchestratorButNoLoadDatabaseFixturesCommand(): void
    {
        $connections = [
            'default'     => 'doctrine.default_connection',
            'monolithic'  => 'doctrine.monolithic_connection',
        ];

        $tables = [
            'default'     => [],
            'monolithic'  => ['table1', 'table3'],
        ];

        $this->setParameter('doctrine.connections', $connections);
        $this->setParameter(
            'kununu_testing.connections.default',
            [
                'load_command_fixtures_classes_namespace' => [],
                'excluded_tables'                         => $tables['default'],
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
            $this->assertOrchestratorServiceExists($connName, $connId, $tables[$connName]);
            $this->assertLoadDatabaseFixturesCommandServiceDoesNotExist($connName);
        }
    }

    public function testThatDoesNotCreatesOrchestrator(): void
    {
        $connections = [
            'default'     => 'doctrine.default_connection',
            'monolithic'  => 'doctrine.monolithic_connection',
        ];

        $this->setParameter('doctrine.connections', $connections);

        $this->compile();

        foreach ($connections as $connName => $connId) {
            $this->assertOrchestratorServiceDoesNotExist($connName);
            $this->assertLoadDatabaseFixturesCommandServiceDoesNotExist($connName);
        }
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new DoctrineCompilerPass());
    }

    /*
     * Returns Orchestrator Service Id
     */
    private function assertOrchestratorServiceExists(string $connName, string $connId, array $excludeTables): string
    {
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
            $excludeTables
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

        return $orchestratorId;
    }

    private function assertOrchestratorServiceDoesNotExist(string $connName): void
    {
        $purgerId = sprintf('kununu_testing.orchestrator.connections.%s.purger', $connName);
        $executorId = sprintf('kununu_testing.orchestrator.connections.%s.executor', $connName);
        $loaderId = sprintf('kununu_testing.orchestrator.connections.%s.loader', $connName);
        $orchestratorId = sprintf('kununu_testing.orchestrator.connections.%s', $connName);

        $this->assertContainerBuilderNotHasService($purgerId);
        $this->assertContainerBuilderNotHasService($executorId);
        $this->assertContainerBuilderNotHasService($loaderId);
        $this->assertContainerBuilderNotHasService($orchestratorId);
    }

    private function assertLoadDatabaseFixturesCommandServiceDoesNotExist(string $connName): void
    {
        $loadDatabaseFixturesCommandId = sprintf('kununu_testing.command.load_fixtures.connections.%s', $connName);

        $this->assertContainerBuilderNotHasService($loadDatabaseFixturesCommandId);
    }
}
