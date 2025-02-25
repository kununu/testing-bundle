<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Unit\DependencyInjection\Compiler;

use Kununu\DataFixtures\Loader\ConnectionFixturesLoader;
use Kununu\TestingBundle\DependencyInjection\Compiler\AbstractConnectionCompilerPass;
use Symfony\Component\DependencyInjection\Reference;

abstract class BaseConnectionCompilerPassTestCase extends BaseLoadFixturesCommandCompilerPassTestCase
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
            sprintf('kununu_testing.%s.default', $this->sectionName),
            [
                'load_command_fixtures_classes_namespace' => $loadCommandFixturesClassesNamespace['default'],
                'excluded_tables'                         => $excludedTables['default'],
            ]
        );
        $this->setParameter(
            sprintf('kununu_testing.%s.persistence', $this->sectionName),
            [
                'load_command_fixtures_classes_namespace' => $loadCommandFixturesClassesNamespace['persistence'],
                'excluded_tables'                         => $excludedTables['persistence'],
            ]
        );
        $this->setParameter(
            sprintf('kununu_testing.%s.monolithic', $this->sectionName),
            [
                'load_command_fixtures_classes_namespace' => $loadCommandFixturesClassesNamespace['monolithic'],
                'excluded_tables'                         => $excludedTables['monolithic'],
            ]
        );
        $this->compile();

        foreach ($connections as $connName => $connId) {
            $purgerId = sprintf('kununu_testing.orchestrator.%s.%s.purger', $this->sectionName, $connName);
            $executorId = sprintf('kununu_testing.orchestrator.%s.%s.executor', $this->sectionName, $connName);
            $loaderId = sprintf('kununu_testing.orchestrator.%s.%s.loader', $this->sectionName, $connName);
            $orchestratorId = sprintf('kununu_testing.orchestrator.%s.%s', $this->sectionName, $connName);
            $consoleCommandId = sprintf('kununu_testing.load_fixtures.%s.%s.command', $this->sectionName, $connName);
            $consoleCommandName = sprintf('kununu_testing:load_fixtures:%s:%s', $this->sectionName, $connName);

            $this->assertPurger($purgerId, $this->getPurgerClass(), new Reference($connId), $excludedTables[$connName]);
            $this->assertExecutor(
                $executorId,
                $this->executorClass,
                new Reference($connId),
                new Reference($purgerId)
            );
            $this->assertLoader($loaderId, $this->loaderClass);
            $this->assertOrchestrator($orchestratorId, $executorId, $loaderId);

            if (in_array($connName, ['default', 'monolithic'])) {
                $this->assertFixturesCommand(
                    $consoleCommandId,
                    $consoleCommandName,
                    $this->commandClass,
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

        foreach ($connections as $connName => $connId) {
            $this->assertContainerBuilderNotHasService(
                sprintf('kununu_testing.orchestrator.%s.%s', $this->sectionName, $connName)
            );
            $this->assertContainerBuilderNotHasService(
                sprintf('kununu_testing.load_fixtures.%s.%s.command', $this->sectionName, $connName)
            );
        }
    }

    public function testCompileWithoutConnections(): void
    {
        $this->compile();

        foreach ($this->container->getServiceIds() as $serviceId) {
            self::assertDoesNotMatchRegularExpression(
                '/^kununu_testing\.orchestrator\.%s\.\w+$/m',
                $this->sectionName,
                $serviceId
            );
            self::assertDoesNotMatchRegularExpression(
                '/^kununu_testing\.orchestrator\.%s\.\w+\.purger$/m',
                $this->sectionName,
                $serviceId
            );
            self::assertDoesNotMatchRegularExpression(
                '/^kununu_testing\.orchestrator\.%s\.\w+\.executor/m',
                $this->sectionName,
                $serviceId
            );
            self::assertDoesNotMatchRegularExpression(
                '/^kununu_testing\.orchestrator\.%s\.\w+\.loader/m',
                $this->sectionName,
                $serviceId
            );
            self::assertDoesNotMatchRegularExpression(
                '/^kununu_testing\.load_fixtures\.%s\.\w+\.command/m',
                $this->sectionName,
                $serviceId
            );
        }
    }

    abstract protected function getCompilerInstance(): AbstractConnectionCompilerPass;

    protected function getLoaderClass(): string
    {
        return ConnectionFixturesLoader::class;
    }
}
