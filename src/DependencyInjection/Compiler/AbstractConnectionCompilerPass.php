<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\DependencyInjection\Compiler;

use Kununu\DataFixtures\Loader\ConnectionFixturesLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

abstract class AbstractConnectionCompilerPass extends AbstractLoadFixturesCommandCompilerPass
{
    private const string DOCTRINE_CONNECTIONS_PARAM = 'doctrine.connections';
    private const string EXCLUDED_TABLES_CONFIG = 'excluded_tables';
    private const string CONNECTION_CONFIG_PARAM = 'kununu_testing.%s.%s';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter(self::DOCTRINE_CONNECTIONS_PARAM)) {
            return;
        }

        $connections = $container->getParameter(self::DOCTRINE_CONNECTIONS_PARAM);

        foreach ($connections as $connName => $connId) {
            $connConfigsParameterName = sprintf(self::CONNECTION_CONFIG_PARAM, $this->sectionName, $connName);

            // Connection is not configured for usage with this bundle
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

    protected function getLoaderClass(): string
    {
        return ConnectionFixturesLoader::class;
    }

    private function buildContainerDefinitions(
        ContainerBuilder $container,
        string $connId,
        string $connName,
        array $connConfig,
    ): void {
        $this->buildLoadFixturesCommand(
            container: $container,
            fixtureType: $this->sectionName,
            orchestratorId: $this->buildOrchestrator($container, $connId, $connName, $connConfig),
            commandClassName: $this->getCommandClass(),
            name: $connName,
            namespace: $connConfig[self::LOAD_COMMAND_CLASSES_NAMESPACE_CONFIG] ?? []
        );
    }

    private function buildOrchestrator(
        ContainerBuilder $container,
        string $id,
        string $connName,
        array $connConfig,
    ): string {
        $this->buildGenericOrchestrator(
            container: $container,
            baseId: $id,
            // Loader Definition will be for the Connection with provided name
            loaderId: sprintf('%s.%s.loader', $this->orchestratorServicePrefix, $connName),
            // Orchestrator definition will be for the Connection with provided name
            orchestratorId: $orchestratorId = sprintf('%s.%s', $this->orchestratorServicePrefix, $connName),
            // Purger Definition for the Connection with provided id
            purgerDefinitionBuilder: fn(ContainerBuilder $container, string $baseId): array => [
                sprintf('%s.%s.purger', $this->orchestratorServicePrefix, $connName),
                new Definition(
                    $this->getPurgerClass(),
                    [
                        new Reference($baseId),
                        $connConfig[self::EXCLUDED_TABLES_CONFIG] ?? [],
                    ]
                ),
            ],
            // Executor Definition for the Connection with provided $id
            executorDefinitionBuilder: fn(ContainerBuilder $container, string $baseId, string $purgerId): array => [
                sprintf('%s.%s.executor', $this->orchestratorServicePrefix, $connName),
                new Definition(
                    $this->getExecutorClass(),
                    [
                        new Reference($baseId),
                        new Reference($purgerId),
                    ]
                ),
            ],
        );

        return $orchestratorId;
    }
}
