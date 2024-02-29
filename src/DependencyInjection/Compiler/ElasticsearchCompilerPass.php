<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\DependencyInjection\Compiler;

use Kununu\DataFixtures\Executor\ElasticsearchExecutor;
use Kununu\DataFixtures\Loader\ElasticsearchFixturesLoader;
use Kununu\DataFixtures\Purger\ElasticsearchPurger;
use Kununu\TestingBundle\Command\LoadElasticsearchFixturesCommand;
use Kununu\TestingBundle\Service\Orchestrator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class ElasticsearchCompilerPass implements CompilerPassInterface
{
    use LoadFixturesCommandsTrait;

    private const SERVICE_PREFIX = 'kununu_testing.orchestrator.elastic_search';
    private const LOAD_COMMAND_FIXTURES_CLASSES_NAMESPACE_CONFIG = 'load_command_fixtures_classes_namespace';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('kununu_testing.elastic_search')) {
            return;
        }

        $indexes = $container->getParameter('kununu_testing.elastic_search');

        foreach ($indexes as $alias => $config) {
            $this->buildContainerDefinitions($container, $alias, $config);
        }
    }

    private function buildContainerDefinitions(ContainerBuilder $containerBuilder, string $alias, array $config): void
    {
        $orchestratorId = $this->buildElasticsearchOrchestrator($containerBuilder, $alias, $config);

        $this->buildLoadFixturesCommand(
            $containerBuilder,
            'elastic_search',
            $orchestratorId,
            LoadElasticsearchFixturesCommand::class,
            $alias,
            $config[self::LOAD_COMMAND_FIXTURES_CLASSES_NAMESPACE_CONFIG] ?? []
        );
    }

    private function buildElasticsearchOrchestrator(ContainerBuilder $container, string $alias, array $config): string
    {
        $id = $config['service'];

        // Purger Definition for Client with provided $id
        $purgerId = sprintf('%s.%s.purger', self::SERVICE_PREFIX, $alias);
        $purgerDefinition = new Definition(
            ElasticsearchPurger::class,
            [
                $client = new Reference($id),
                $indexName = $config['index_name'],
            ]
        );
        $container->setDefinition($purgerId, $purgerDefinition);

        // Executor Definition for Client with provided $id
        $executorId = sprintf('%s.%s.executor', self::SERVICE_PREFIX, $alias);
        $executorDefinition = new Definition(
            ElasticsearchExecutor::class,
            [
                $client,
                $indexName,
                new Reference($purgerId),
            ]
        );
        $container->setDefinition($executorId, $executorDefinition);

        // Loader definition for Client with provided $id
        $loaderId = sprintf('%s.%s.loader', self::SERVICE_PREFIX, $alias);
        $loaderDefinition = new Definition(ElasticsearchFixturesLoader::class);
        $container->setDefinition($loaderId, $loaderDefinition);

        // Orchestrator definition for Client with provided $id
        $orchestratorDefinition = new Definition(
            Orchestrator::class,
            [
                new Reference($executorId),
                new Reference($loaderId),
            ]
        );
        $orchestratorDefinition->setPublic(true);

        $container->setDefinition(
            $orchestratorId = sprintf('%s.%s', self::SERVICE_PREFIX, $alias),
            $orchestratorDefinition
        );

        return $orchestratorId;
    }
}
