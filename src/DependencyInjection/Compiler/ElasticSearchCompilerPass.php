<?php declare(strict_types=1);

namespace Kununu\TestingBundle\DependencyInjection\Compiler;

use Elasticsearch\Client;
use Kununu\DataFixtures\Executor\ElasticSearchExecutor;
use Kununu\DataFixtures\Loader\ElasticSearchFixturesLoader;
use Kununu\DataFixtures\Purger\ElasticSearchPurger;
use Kununu\TestingBundle\Service\Orchestrator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class ElasticSearchCompilerPass implements CompilerPassInterface
{
    private const SERVICE_PREFIX = 'kununu_testing.orchestrator.elastic_search';

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('kununu_testing.elastic_search')) {
            return;
        }

        $indexes = $container->getParameter('kununu_testing.elastic_search');

        foreach ($indexes as $alias => $config) {
            $this->buildElasticSearchOrchestrator($container, $alias, $config['index_name'], $config['service']);
        }
    }

    private function buildElasticSearchOrchestrator(ContainerBuilder $container, string $alias, string $indexName, string $id) : void
    {
        /** @var Client $client */
        $client = new Reference($id);

        // Purger Definition
        $purgerId = sprintf('%s.%s.purger',self::SERVICE_PREFIX, $alias);
        $purgerDefinition = new Definition(ElasticSearchPurger::class, [$client, $indexName]);
        $container->setDefinition($purgerId, $purgerDefinition);

        // Executor Definition
        $executorId = sprintf('%s.%s.executor',self::SERVICE_PREFIX, $alias);
        $executorDefinition = new Definition(ElasticSearchExecutor::class, [$client, new Reference($purgerId)]);
        $container->setDefinition($executorId, $executorDefinition);

        // Loader definition
        $loaderId = sprintf('%s.%s.loader',self::SERVICE_PREFIX, $alias);
        $loaderDefinition = new Definition(ElasticSearchFixturesLoader::class);
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

        $container->setDefinition(sprintf('%s.%s', self::SERVICE_PREFIX, $alias), $connectionOrchestratorDefinition);
    }
}
