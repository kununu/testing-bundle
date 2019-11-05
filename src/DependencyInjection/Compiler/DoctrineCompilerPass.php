<?php declare(strict_types=1);

namespace Kununu\TestingBundle\DependencyInjection\Compiler;

use Doctrine\DBAL\Connection;
use Kununu\DataFixtures\Executor\ConnectionExecutor;
use Kununu\DataFixtures\Loader\ConnectionFixturesLoader;
use Kununu\DataFixtures\Purger\ConnectionPurger;
use Kununu\TestingBundle\Command\LoadDatabaseFixturesCommand;
use Kununu\TestingBundle\Service\Orchestrator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class DoctrineCompilerPass implements CompilerPassInterface
{
    private const EXCLUDED_TABLES_CONFIG = 'excluded_tables';
    private const LOAD_COMMAND_FIXTURES_CLASSES_NAMESPACE_CONFIG = 'load_command_fixtures_classes_namespace';

    private const ORCHESTRATOR_SERVICE_PREFIX          = 'kununu_testing.orchestrator.connections';

    private const LOAD_FIXTURES_COMMAND_SERVICE_PREFIX = 'kununu_testing.command.load_fixtures.connections';
    private const LOAD_FIXTURES_COMMAND_PREFIX = 'kununu_testing:load_fixtures:connections';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('doctrine.connections')) {
            return;
        }

        $connections = $container->getParameter('doctrine.connections');

        foreach ($connections as $connName => $connId) {
            $connConfigsParameterName = sprintf('kununu_testing.connections.%s', $connName);

            // Connection is not configured for kununu\testing-bundle
            if (! $container->hasParameter($connConfigsParameterName)) {
                continue;
            }

            $connConfigs = $container->getParameter($connConfigsParameterName);

            $orchestratorId = $this->buildConnectionOrchestrator($container, $connName, $connConfigs, $connId);
            $this->buildConnectionLoadFixturesCommand($container, $connName, $connConfigs, $orchestratorId);
        }
    }

    private function buildConnectionOrchestrator(ContainerBuilder $container, string $connName, array $connConfigs, string $id): string
    {
        $excludedTables = !empty($connConfigs[self::EXCLUDED_TABLES_CONFIG]) ? $connConfigs[self::EXCLUDED_TABLES_CONFIG] : [];

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
                new Reference($purgerId),
                new Reference($loaderId),
            ]
        );
        $connectionOrchestratorDefinition->setPublic(true);

        $orchestratorId = sprintf('%s.%s', self::ORCHESTRATOR_SERVICE_PREFIX, $connName);

        $container->setDefinition($orchestratorId, $connectionOrchestratorDefinition);

        return $orchestratorId;
    }

    private function buildConnectionLoadFixturesCommand(
        ContainerBuilder $container,
        string $connName,
        array $connConfigs,
        string $orchestratorId
    ): void {
        // Connection does not have fixtures configured for LoadDatabaseFixturesCommand
        if (! isset($connConfigs[self::LOAD_COMMAND_FIXTURES_CLASSES_NAMESPACE_CONFIG]) ||
            empty($connConfigs[self::LOAD_COMMAND_FIXTURES_CLASSES_NAMESPACE_CONFIG])
        ) {
            return;
        }

        $connectionLoadFixturesDefinition = new Definition(
            LoadDatabaseFixturesCommand::class,
            [
                $connName,
                new Reference($orchestratorId),
                $connConfigs[self::LOAD_COMMAND_FIXTURES_CLASSES_NAMESPACE_CONFIG]
            ]
        );
        $connectionLoadFixturesDefinition->setPublic(true);
        $connectionLoadFixturesDefinition->setTags(
            ['console.command' => [['command' => sprintf('%s:%s', self::LOAD_FIXTURES_COMMAND_PREFIX, $connName)]]]
        );

        $container->setDefinition(
            sprintf('%s.%s', self::LOAD_FIXTURES_COMMAND_SERVICE_PREFIX, $connName),
            $connectionLoadFixturesDefinition
        );
    }
}
