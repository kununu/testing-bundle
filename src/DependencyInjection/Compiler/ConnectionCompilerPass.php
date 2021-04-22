<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\DependencyInjection\Compiler;

use Doctrine\DBAL\Connection;
use Kununu\DataFixtures\Executor\ConnectionExecutor;
use Kununu\DataFixtures\Loader\ConnectionFixturesLoader;
use Kununu\DataFixtures\Purger\ConnectionPurger;
use Kununu\TestingBundle\Command\LoadConnectionFixturesCommand;
use Kununu\TestingBundle\Service\Orchestrator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class ConnectionCompilerPass implements CompilerPassInterface
{
    use LoadFixturesCommandsTrait;

    private const EXCLUDED_TABLES_CONFIG = 'excluded_tables';
    private const LOAD_COMMAND_FIXTURES_CLASSES_NAMESPACE_CONFIG = 'load_command_fixtures_classes_namespace';
    private const ORCHESTRATOR_SERVICE_PREFIX = 'kununu_testing.orchestrator.connections';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('doctrine.connections')) {
            return;
        }

        $connections = $container->getParameter('doctrine.connections');

        foreach ($connections as $connName => $connId) {
            $connConfigsParameterName = sprintf('kununu_testing.connections.%s', $connName);

            // Connection is not configured for kununu\testing-bundle
            if (!$container->hasParameter($connConfigsParameterName)) {
                continue;
            }

            $this->buildContainerDefinitions(
                $container,
                $connId,
                $connName,
                $container->getParameter($connConfigsParameterName)
            );
        }
    }

    private function buildContainerDefinitions(
        ContainerBuilder $container,
        string $connId,
        string $connName,
        array $connConfig
    ): void {
        $orchestratorId = $this->buildConnectionOrchestrator($container, $connId, $connName, $connConfig);

        $this->buildLoadFixturesCommand(
            $container,
            'connections',
            $orchestratorId,
            LoadConnectionFixturesCommand::class,
            $connName,
            $connConfig[self::LOAD_COMMAND_FIXTURES_CLASSES_NAMESPACE_CONFIG] ?? []
        );
    }

    private function buildConnectionOrchestrator(
        ContainerBuilder $container,
        string $id,
        string $connName,
        array $connConfig
    ): string {
        $excludedTables = $connConfig[self::EXCLUDED_TABLES_CONFIG] ?? [];

        /** @var Connection $connection */
        $connection = new Reference($id);

        // Purger Definition for the Connection with provided $id
        $purgerId = sprintf('%s.%s.purger', self::ORCHESTRATOR_SERVICE_PREFIX, $connName);
        $purgerDefinition = new Definition(ConnectionPurger::class, [$connection, $excludedTables]);
        $container->setDefinition($purgerId, $purgerDefinition);

        // Executor Definition for the Connection with provided $id
        $executorId = sprintf('%s.%s.executor', self::ORCHESTRATOR_SERVICE_PREFIX, $connName);
        $executorDefinition = new Definition(ConnectionExecutor::class, [$connection, new Reference($purgerId)]);
        $container->setDefinition($executorId, $executorDefinition);

        // Loader Definition for the Connection with provided $id
        $loaderId = sprintf('%s.%s.loader', self::ORCHESTRATOR_SERVICE_PREFIX, $connName);
        $loaderDefinition = new Definition(ConnectionFixturesLoader::class);
        $container->setDefinition($loaderId, $loaderDefinition);

        $connectionOrchestratorDefinition = new Definition(
            Orchestrator::class,
            [
                new Reference($executorId),
                new Reference($loaderId),
            ]
        );
        $connectionOrchestratorDefinition->setPublic(true);

        $orchestratorId = sprintf('%s.%s', self::ORCHESTRATOR_SERVICE_PREFIX, $connName);

        $container->setDefinition($orchestratorId, $connectionOrchestratorDefinition);

        return $orchestratorId;
    }
}
