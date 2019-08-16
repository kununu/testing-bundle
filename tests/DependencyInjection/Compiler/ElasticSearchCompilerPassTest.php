<?php declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\DependencyInjection\Compiler;

use Kununu\DataFixtures\Executor\ElasticSearchExecutor;
use Kununu\DataFixtures\Loader\ElasticSearchFixturesLoader;
use Kununu\DataFixtures\Purger\ElasticSearchPurger;
use Kununu\TestingBundle\DependencyInjection\Compiler\ElasticSearchCompilerPass;
use Kununu\TestingBundle\Service\Orchestrator;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class ElasticSearchCompilerPassTest extends AbstractCompilerPassTestCase
{
    public function testThatCreatesOrchestratorForEachDoctrineConnection()
    {
        $indexes = [
            'alias_1' => ['index_name' => 'index1', 'service' => 'elastic_search_service_1'],
            'alias_2' => ['index_name' => 'index2', 'service' => 'elastic_search_service_1'],
            'alias_3' => ['index_name' => 'index1', 'service' => 'elastic_search_service_2'],
        ];

        $this->setParameter('kununu_testing.elastic_search', $indexes);

        $this->compile();

        foreach ($indexes as $alias => $config) {
            $purgerId = sprintf('kununu_testing.orchestrator.elastic_search.%s.purger', $alias);
            $executorId = sprintf('kununu_testing.orchestrator.elastic_search.%s.executor', $alias);
            $loaderId = sprintf('kununu_testing.orchestrator.elastic_search.%s.loader', $alias);
            $orchestratorId = sprintf('kununu_testing.orchestrator.elastic_search.%s', $alias);

            $indexName = $config['index_name'];
            $elasticSearchClient = $config['service'];

            $this->assertContainerBuilderHasServiceDefinitionWithArgument(
                $purgerId,
                0,
                new Reference($elasticSearchClient)
            );
            $this->assertContainerBuilderHasServiceDefinitionWithArgument(
                $purgerId,
                1,
                $indexName
            );
            $this->assertContainerBuilderHasService(
                $purgerId,
                ElasticSearchPurger::class
            );
            $this->assertTrue($this->container->getDefinition($purgerId)->isPrivate());

            $this->assertContainerBuilderHasServiceDefinitionWithArgument(
                $executorId,
                0,
                new Reference($elasticSearchClient)
            );
            $this->assertContainerBuilderHasServiceDefinitionWithArgument(
                $executorId,
                1,
                new Reference($purgerId)
            );
            $this->assertContainerBuilderHasService(
                $executorId,
                ElasticSearchExecutor::class
            );
            $this->assertTrue($this->container->getDefinition($executorId)->isPrivate());

            $this->assertContainerBuilderHasService(
                $loaderId,
                ElasticSearchFixturesLoader::class
            );
            $this->assertTrue($this->container->getDefinition($loaderId)->isPrivate());

            $this->assertContainerBuilderHasServiceDefinitionWithArgument(
                $orchestratorId,
                0,
                new Reference($executorId)
            );
            $this->assertContainerBuilderHasServiceDefinitionWithArgument(
                $orchestratorId,
                1,
                new Reference($purgerId)
            );
            $this->assertContainerBuilderHasServiceDefinitionWithArgument(
                $orchestratorId,
                2,
                new Reference($loaderId)
            );
            $this->assertContainerBuilderHasService(
                $orchestratorId,
                Orchestrator::class
            );
            $this->assertTrue($this->container->getDefinition($orchestratorId)->isPublic());
        }
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ElasticSearchCompilerPass());
    }
}
