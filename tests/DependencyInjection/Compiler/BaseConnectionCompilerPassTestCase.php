<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\DependencyInjection\Compiler;

use Kununu\DataFixtures\Loader\ConnectionFixturesLoader;
use Kununu\TestingBundle\DependencyInjection\Compiler\AbstractConnectionCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

abstract class BaseConnectionCompilerPassTestCase extends BaseCompilerPassTestCase
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

        $sectionName = $this->getSectionName();
        $this->setParameter(
            sprintf('kununu_testing.%s.default', $sectionName),
            [
                'load_command_fixtures_classes_namespace' => $loadCommandFixturesClassesNamespace['default'],
                'excluded_tables'                         => $excludedTables['default'],
            ]
        );
        $this->setParameter(
            sprintf('kununu_testing.%s.persistence', $sectionName),
            [
                'load_command_fixtures_classes_namespace' => $loadCommandFixturesClassesNamespace['persistence'],
                'excluded_tables'                         => $excludedTables['persistence'],
            ]
        );
        $this->setParameter(
            sprintf('kununu_testing.%s.monolithic', $sectionName),
            [
                'load_command_fixtures_classes_namespace' => $loadCommandFixturesClassesNamespace['monolithic'],
                'excluded_tables'                         => $excludedTables['monolithic'],
            ]
        );
        $this->compile();

        foreach ($connections as $connName => $connId) {
            $purgerId = sprintf('kununu_testing.orchestrator.%s.%s.purger', $sectionName, $connName);
            $executorId = sprintf('kununu_testing.orchestrator.%s.%s.executor', $sectionName, $connName);
            $loaderId = sprintf('kununu_testing.orchestrator.%s.%s.loader', $sectionName, $connName);
            $orchestratorId = sprintf('kununu_testing.orchestrator.%s.%s', $sectionName, $connName);
            $consoleCommandId = sprintf('kununu_testing.load_fixtures.%s.%s.command', $sectionName, $connName);
            $consoleCommandName = sprintf('kununu_testing:load_fixtures:%s:%s', $sectionName, $connName);

            $this->assertPurger($purgerId, $this->getPurgerClass(), new Reference($connId), $excludedTables[$connName]);
            $this->assertExecutor($executorId, $this->getExecutorClass(), new Reference($connId), new Reference($purgerId));
            $this->assertLoader($loaderId, ConnectionFixturesLoader::class);
            $this->assertOrchestrator($orchestratorId, $executorId, $loaderId);

            if (in_array($connName, ['default', 'monolithic'])) {
                $this->assertFixturesCommand(
                    $consoleCommandId,
                    $consoleCommandName,
                    $this->getLoadFixturesCommandClass(),
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
            'default'    => 'doctrine.default_connection',
            'monolithic' => 'doctrine.monolithic_connection',
        ];

        $this->setParameter('doctrine.connections', $connections);

        $this->compile();

        $sectionName = $this->getSectionName();
        foreach ($connections as $connName => $connId) {
            $this->assertContainerBuilderNotHasService(sprintf('kununu_testing.orchestrator.%s.%s', $sectionName, $connName));
            $this->assertContainerBuilderNotHasService(sprintf('kununu_testing.load_fixtures.%s.%s.command', $sectionName, $connName));
        }
    }

    public function testCompileWithoutConnections(): void
    {
        $this->compile();

        $sectionName = $this->getSectionName();
        foreach ($this->container->getServiceIds() as $serviceId) {
            $this->assertThatDoesNotMatchRegularExpression('/^kununu_testing\.orchestrator\.%s\.\w+$/m', $sectionName, $serviceId);
            $this->assertThatDoesNotMatchRegularExpression('/^kununu_testing\.orchestrator\.%s\.\w+\.purger$/m', $sectionName, $serviceId);
            $this->assertThatDoesNotMatchRegularExpression('/^kununu_testing\.orchestrator\.%s\.\w+\.executor/m', $sectionName, $serviceId);
            $this->assertThatDoesNotMatchRegularExpression('/^kununu_testing\.orchestrator\.%s\.\w+\.loader/m', $sectionName, $serviceId);
            $this->assertThatDoesNotMatchRegularExpression('/^kununu_testing\.load_fixtures\.%s\.\w+\.command/m', $sectionName, $serviceId);
        }
    }

    abstract protected function getCompilerInstance(): AbstractConnectionCompilerPass;

    abstract protected function getSectionName(): string;

    abstract protected function getPurgerClass(): string;

    abstract protected function getExecutorClass(): string;

    abstract protected function getLoadFixturesCommandClass(): string;

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass($this->getCompilerInstance());
    }
}
