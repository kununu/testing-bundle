<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\DependencyInjection\Compiler;

use Kununu\DataFixtures\Executor\ConnectionExecutor;
use Kununu\DataFixtures\Loader\ConnectionFixturesLoader;
use Kununu\DataFixtures\Purger\ConnectionPurger;
use Kununu\TestingBundle\Command\LoadConnectionFixturesCommand;
use Kununu\TestingBundle\DependencyInjection\Compiler\ConnectionCompilerPass;
use Kununu\TestingBundle\Service\Orchestrator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class ConnectionCompilerPassTest extends BaseCompilerPassTestCase
{
    public function testCompileWithConfigurations(): void
    {
        $connections = [
            'default'     => 'doctrine.default_connection',
            'persistence' => 'doctrine.persistence_connection',
            'monolithic'  => 'doctrine.monolithic_connection',
        ];

        $excludedTables = [
            'default'     => [],
            'persistence' => ['table1'],
            'monolithic'  => ['table1', 'table3'],
        ];

        $loadCommandFixturesClassesNamespace = [
            'default'     => ['App\DataFixtures\Fixture1'],
            'persistence' => [],
            'monolithic'  => ['App\DataFixtures\Fixture2', 'App\DataFixtures\Fixture3'],
        ];

        $this->setParameter('doctrine.connections', $connections);

        $this->setParameter(
            'kununu_testing.connections.default',
            [
                'load_command_fixtures_classes_namespace' => $loadCommandFixturesClassesNamespace['default'],
                'excluded_tables'                         => $excludedTables['default'],
            ]
        );
        $this->setParameter(
            'kununu_testing.connections.persistence',
            [
                'load_command_fixtures_classes_namespace' => $loadCommandFixturesClassesNamespace['persistence'],
                'excluded_tables'                         => $excludedTables['persistence'],
            ]
        );
        $this->setParameter(
            'kununu_testing.connections.monolithic',
            [
                'load_command_fixtures_classes_namespace' => $loadCommandFixturesClassesNamespace['monolithic'],
                'excluded_tables'                         => $excludedTables['monolithic'],
            ]
        );
        $this->compile();

        foreach ($connections as $connName => $connId) {
            $purgerId = sprintf('kununu_testing.orchestrator.connections.%s.purger', $connName);
            $executorId = sprintf('kununu_testing.orchestrator.connections.%s.executor', $connName);
            $loaderId = sprintf('kununu_testing.orchestrator.connections.%s.loader', $connName);
            $orchestratorId = sprintf('kununu_testing.orchestrator.connections.%s', $connName);
            $consoleCommandId = sprintf('kununu_testing.load_fixtures.connections.%s.command', $connName);
            $consoleCommandName = sprintf('kununu_testing:load_fixtures:connections:%s', $connName);

            $this->assertPurger($purgerId, $connId, $excludedTables[$connName]);
            $this->assertExecutor($executorId, $connId, $purgerId);
            $this->assertLoader($loaderId);
            $this->assertOrchestrator($orchestratorId, $executorId, $purgerId, $loaderId);

            if (in_array($connName, ['default', 'monolithic'])) {
                $this->assertFixturesCommand(
                    $consoleCommandId,
                    $consoleCommandName,
                    LoadConnectionFixturesCommand::class,
                    $connName,
                    $orchestratorId,
                    $loadCommandFixturesClassesNamespace[$connName]
                );
            } else {
                $this->assertContainerBuilderNotHasService($consoleCommandId);
            }
        }
    }

    public function testCompileWithoutConfigurations(): void
    {
        $connections = [
            'default'     => 'doctrine.default_connection',
            'monolithic'  => 'doctrine.monolithic_connection',
        ];

        $this->setParameter('doctrine.connections', $connections);

        $this->compile();

        foreach ($connections as $connName => $connId) {
            $this->assertContainerBuilderNotHasService(sprintf('kununu_testing.orchestrator.connections.%s', $connName));
            $this->assertContainerBuilderNotHasService(sprintf('kununu_testing.load_fixtures.connections.%s.command', $connName));
        }
    }

    public function testCompileWithoutConnections(): void
    {
        $this->compile();

        foreach ($this->container->getServiceIds() as $serviceId) {
            $this->assertNotRegExp('/^kununu_testing\.orchestrator\.connections\.\w+$/m', $serviceId);
            $this->assertNotRegExp('/^kununu_testing\.orchestrator\.connections\.\w+\.purger$/m', $serviceId);
            $this->assertNotRegExp('/^kununu_testing\.orchestrator\.connections\.\w+\.executor/m', $serviceId);
            $this->assertNotRegExp('/^kununu_testing\.orchestrator\.connections\.\w+\.loader/m', $serviceId);
            $this->assertNotRegExp('/^kununu_testing\.load_fixtures\.connections\.\w+\.command/m', $serviceId);
        }
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ConnectionCompilerPass());
    }

    private function assertPurger(string $purgerId, string $connId, array $excludeTables): void
    {
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
    }

    private function assertExecutor(string $executorId, string $connId, string $purgerId): void
    {
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
    }

    private function assertLoader(string $loaderId): void
    {
        $this->assertContainerBuilderHasService(
            $loaderId,
            ConnectionFixturesLoader::class
        );
        $this->assertTrue($this->container->getDefinition($loaderId)->isPrivate());
    }

    private function assertOrchestrator(string $orchestratorId, string $executorId, string $purgerId, string $loaderId): void
    {
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            $orchestratorId,
            0,
            new Reference($executorId)
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            $orchestratorId,
            1,
            new Reference($loaderId)
        );
        $this->assertContainerBuilderHasService(
            $orchestratorId,
            Orchestrator::class
        );
        $this->assertTrue($this->container->getDefinition($orchestratorId)->isPublic());
    }
}
