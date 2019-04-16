<?php declare(strict_types=1);

namespace Kununu\TestingBundle\DependencyInjection\Compiler;

use Doctrine\DBAL\Connection;
use Kununu\DataFixtures\Executor\ConnectionExecutor;
use Kununu\DataFixtures\Loader\ConnectionFixturesLoader;
use Kununu\DataFixtures\Purger\ConnectionPurger;
use Kununu\TestingBundle\Service\Orchestrator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class DoctrineCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('doctrine.connections')) {
            return;
        }

        $connections = $container->getParameter('doctrine.connections');

        foreach ($connections as $connName => $connId) {
            $this->buildConnectionOrchestrator($container, $connName, $connId);
        }
    }

    private function buildConnectionOrchestrator(ContainerBuilder $container, string $connName, string $id) : void
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
        $purgerId = sprintf('kununu_testing.orchestrator.connections.%s.purger', $connName);
        $purgerDefinition = new Definition(ConnectionPurger::class, [$connection, $excludedTables]);
        $container->setDefinition($purgerId, $purgerDefinition);

        // Executor Definition for the Connection with provided $id
        $executorId = sprintf('kununu_testing.orchestrator.connections.%s.executor', $connName);
        $executorDefinition = new Definition(ConnectionExecutor::class, [$connection, new Reference($purgerId)]);
        $container->setDefinition($executorId, $executorDefinition);

        // Loader Definition for the Connection with provided $id
        $loaderId = sprintf('kununu_testing.orchestrator.connections.%s.loader', $connName);
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

        $container->setDefinition(sprintf('kununu_testing.orchestrator.connections.%s', $connName), $connectionOrchestratorDefinition);
    }
}
