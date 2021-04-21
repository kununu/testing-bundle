<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\DependencyInjection\Compiler;

use Elasticsearch\Client;
use Kununu\DataFixtures\Executor\ElasticSearchExecutor;
use Kununu\DataFixtures\Loader\ElasticSearchFixturesLoader;
use Kununu\DataFixtures\Purger\ElasticSearchPurger;
use Kununu\TestingBundle\Command\LoadElasticsearchFixturesCommand;
use Kununu\TestingBundle\Service\Orchestrator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class ElasticSearchCompilerPass implements CompilerPassInterface
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
        $orchestratorId = $this->buildElasticSearchOrchestrator($containerBuilder, $alias, $config);

        $this->buildLoadFixturesCommand(
            $containerBuilder,
            $orchestratorId,
            LoadElasticsearchFixturesCommand::class,
            $alias,
            $config[self::LOAD_COMMAND_FIXTURES_CLASSES_NAMESPACE_CONFIG] ?? []
        );
    }

    private function buildElasticSearchOrchestrator(ContainerBuilder $container, string $alias, array $config): string
    {
        $indexName = $config['index_name'];
        $id = $config['service'];

        /** @var Client $client */
        $client = new Reference($id);

        // Purger Definition
        $purgerId = sprintf('%s.%s.purger', self::SERVICE_PREFIX, $alias);
        $purgerDefinition = new Definition(ElasticSearchPurger::class, [$client, $indexName]);
        $container->setDefinition($purgerId, $purgerDefinition);

        // Executor Definition
        $executorId = sprintf('%s.%s.executor', self::SERVICE_PREFIX, $alias);
        $executorDefinition = new Definition(ElasticSearchExecutor::class, [$client, $indexName, new Reference($purgerId)]);
        $container->setDefinition($executorId, $executorDefinition);

        // Loader definition
        $loaderId = sprintf('%s.%s.loader', self::SERVICE_PREFIX, $alias);
        $loaderDefinition = new Definition(ElasticSearchFixturesLoader::class);
        $container->setDefinition($loaderId, $loaderDefinition);

        $connectionOrchestratorDefinition = new Definition(
            Orchestrator::class,
            [
                new Reference($executorId),
                new Reference($loaderId),
            ]
        );
        $connectionOrchestratorDefinition->setPublic(true);

        $orchestratorId = sprintf('%s.%s', self::SERVICE_PREFIX, $alias);

        $container->setDefinition($orchestratorId, $connectionOrchestratorDefinition);

        return $orchestratorId;
    }
}
