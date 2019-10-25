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
    private const ORCHESTRATOR_SERVICE_PREFIX = 'kununu_testing.orchestrator.connections';
    private const LOAD_FIXTURES_COMMAND_SERVICE_PREFIX = 'kununu_testing.command.load_fixtures.connections';

    private const LOAD_FIXTURES_COMMAND_PREFIX = 'kununu_testing:load_fixtures:connections';


    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('doctrine.connections')) {
            return;
        }

        $connections = $container->getParameter('doctrine.connections');

        foreach ($connections as $connName => $connId) {
            $this->buildConnectionOrchestrator($container, $connName, $connId);
            $this->buildConnectionLoadFixturesCommand($container, $connName);
        }
    }

    private function buildConnectionOrchestrator(ContainerBuilder $container, string $connName, string $id): void
    {
        $excludedTables = [];

        $connConfigsParameterName = sprintf('kununu_testing.connections.%s', $connName);

        if ($container->hasParameter($connConfigsParameterName)) {
            $connConfigs = $container->getParameter($connConfigsParameterName);
            $excludedTables = !empty($connConfigs['excluded_tables']) ? $connConfigs['excluded_tables'] : [];
        }

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

        $container->setDefinition(
            sprintf('%s.%s', self::ORCHESTRATOR_SERVICE_PREFIX, $connName),
            $connectionOrchestratorDefinition
        );
    }

    private function buildConnectionLoadFixturesCommand(ContainerBuilder $container, string $connName): void
    {
        $connectionLoadFixturesDefinition = new Definition(LoadDatabaseFixturesCommand::class, [$connName]);
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
