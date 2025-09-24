<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\DependencyInjection\Compiler;

use Kununu\DataFixtures\Executor\DynamoDbExecutor;
use Kununu\DataFixtures\Loader\DynamoDbFixturesLoader;
use Kununu\DataFixtures\Purger\DynamoDbPurger;
use Kununu\TestingBundle\Command\LoadDynamoDbFixturesCommand;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class DynamoDbCompilerPass extends AbstractLoadFixturesCommandCompilerPass
{
    private const string PARAMETER = 'kununu_testing.dynamo_db';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter(self::PARAMETER)) {
            return;
        }

        $dynamoDbConfigs = $container->getParameter(self::PARAMETER);

        foreach ($dynamoDbConfigs as $alias => $config) {
            $this->buildContainerDefinitions($container, $alias, $config);
        }
    }

    protected function getSectionName(): string
    {
        return 'dynamo_db';
    }

    protected function getCommandClass(): string
    {
        return LoadDynamoDbFixturesCommand::class;
    }

    protected function getPurgerClass(): string
    {
        return DynamoDbPurger::class;
    }

    protected function getExecutorClass(): string
    {
        return DynamoDbExecutor::class;
    }

    protected function getLoaderClass(): string
    {
        return DynamoDbFixturesLoader::class;
    }

    private function buildContainerDefinitions(ContainerBuilder $containerBuilder, string $alias, array $config): void
    {
        $this->buildLoadFixturesCommand(
            container: $containerBuilder,
            fixtureType: $this->getSectionName(),
            orchestratorId: $this->buildOrchestrator($containerBuilder, $alias, $config),
            commandClassName: $this->getCommandClass(),
            name: $alias,
            namespace: $config[self::LOAD_COMMAND_CLASSES_NAMESPACE_CONFIG] ?? []
        );
    }

    private function buildOrchestrator(ContainerBuilder $container, string $alias, array $config): string
    {
        $tableNames = $this->resolveTableNames($config);

        $this->registerOrchestrator(
            container: $container,
            baseId: $config['service'],
            // Loader Definition will be for DynamoDB client with provided $alias
            loaderId: sprintf('%s.%s.loader', $this->orchestratorServicePrefix, $alias),
            // Orchestrator definition will be for DynamoDB client with provided $alias
            orchestratorId: $orchestratorId = sprintf('%s.%s', $this->orchestratorServicePrefix, $alias),
            // Purger Definition for DynamoDB client with provided alias
            purgerDefinitionBuilder: fn(ContainerBuilder $container, string $baseId): array => [
                sprintf('%s.%s.purger', $this->orchestratorServicePrefix, $alias),
                new Definition(
                    $this->getPurgerClass(),
                    [
                        new Reference($baseId),
                        $tableNames,
                    ]
                ),
            ],
            // Executor Definition for DynamoDB client with provided alias
            executorDefinitionBuilder: fn(ContainerBuilder $container, string $baseId, string $purgerId): array => [
                sprintf('%s.%s.executor', $this->orchestratorServicePrefix, $alias),
                new Definition(
                    $this->getExecutorClass(),
                    [
                        new Reference($baseId),
                        new Reference($purgerId),
                    ]
                ),
            ],
            loaderClass: $this->getLoaderClass(),
        );

        return $orchestratorId;
    }

    private function resolveTableNames(array $config): array
    {
        if (!empty($config['table_names'])) {
            return $config['table_names'];
        }

        return [];
    }
}
