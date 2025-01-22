<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

abstract class AbstractElasticCompilerPass extends AbstractLoadFixturesCommandCompilerPass
{
    private const string PARAMETER = 'kununu_testing.%s';

    private readonly string $parameterName;

    public function __construct()
    {
        parent::__construct();
        $this->parameterName = sprintf(self::PARAMETER, $this->sectionName);
    }

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter($this->parameterName)) {
            return;
        }

        $indexes = $container->getParameter($this->parameterName);

        foreach ($indexes as $alias => $config) {
            $this->buildContainerDefinitions($container, $alias, $config);
        }
    }

    private function buildContainerDefinitions(ContainerBuilder $containerBuilder, string $alias, array $config): void
    {
        $this->buildLoadFixturesCommand(
            container: $containerBuilder,
            fixtureType: $this->sectionName,
            orchestratorId: $this->buildOrchestrator($containerBuilder, $alias, $config),
            commandClassName: $this->commandClass,
            name: $alias,
            namespace: $config[self::LOAD_COMMAND_CLASSES_NAMESPACE_CONFIG] ?? []
        );
    }

    private function buildOrchestrator(ContainerBuilder $container, string $alias, array $config): string
    {
        $this->buildGenericOrchestrator(
            container: $container,
            baseId: $config['service'],
            // Loader Definition will be for Client with provided $id
            loaderId: sprintf('%s.%s.loader', $this->orchestratorServicePrefix, $alias),
            // Orchestrator definition will be for Client with provided $id
            orchestratorId: $orchestratorId = sprintf('%s.%s', $this->orchestratorServicePrefix, $alias),
            // Purger Definition for Client with provided id
            purgerDefinitionBuilder: fn(ContainerBuilder $container, string $baseId): array => [
                sprintf('%s.%s.purger', $this->orchestratorServicePrefix, $alias),
                new Definition(
                    $this->purgerClass,
                    [
                        new Reference($baseId),
                        $config['index_name'],
                    ]
                ),
            ],
            // Executor Definition for Client with provided id
            executorDefinitionBuilder: fn(ContainerBuilder $container, string $baseId, string $purgerId): array => [
                sprintf('%s.%s.executor', $this->orchestratorServicePrefix, $alias),
                new Definition(
                    $this->executorClass,
                    [
                        new Reference($baseId),
                        $config['index_name'],
                        new Reference($purgerId),
                    ]
                ),
            ],
        );

        return $orchestratorId;
    }
}
