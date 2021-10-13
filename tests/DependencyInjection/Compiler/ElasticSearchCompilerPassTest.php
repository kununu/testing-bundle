<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\DependencyInjection\Compiler;

use Kununu\DataFixtures\Executor\ElasticSearchExecutor;
use Kununu\DataFixtures\Loader\ElasticSearchFixturesLoader;
use Kununu\DataFixtures\Purger\ElasticSearchPurger;
use Kununu\TestingBundle\Command\LoadElasticsearchFixturesCommand;
use Kununu\TestingBundle\DependencyInjection\Compiler\ElasticSearchCompilerPass;
use Kununu\TestingBundle\Service\Orchestrator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class ElasticSearchCompilerPassTest extends BaseCompilerPassTestCase
{
    public function testCompile(): void
    {
        $indexes = [
            'alias_1' => [
                'index_name' => 'index1',
                'service'    => 'elastic_search_service_1',
            ],
            'alias_2' => [
                'load_command_fixtures_classes_namespace' => [],
                'index_name'                              => 'index2',
                'service'                                 => 'elastic_search_service_1',
            ],
            'alias_3' => [
                'load_command_fixtures_classes_namespace' => [
                    'App/DataFixtures/Fixture1',
                    'App/DataFixtures/Fixture2',
                ],
                'index_name' => 'index1',
                'service'    => 'elastic_search_service_2',
            ],
        ];

        $this->setParameter('kununu_testing.elastic_search', $indexes);

        $this->compile();

        foreach ($indexes as $alias => $config) {
            $purgerId = sprintf('kununu_testing.orchestrator.elastic_search.%s.purger', $alias);
            $executorId = sprintf('kununu_testing.orchestrator.elastic_search.%s.executor', $alias);
            $loaderId = sprintf('kununu_testing.orchestrator.elastic_search.%s.loader', $alias);
            $orchestratorId = sprintf('kununu_testing.orchestrator.elastic_search.%s', $alias);
            $consoleCommandId = sprintf('kununu_testing.load_fixtures.elastic_search.%s.command', $alias);
            $consoleCommandName = sprintf('kununu_testing:load_fixtures:elastic_search:%s', $alias);

            $indexName = $config['index_name'];
            $elasticSearchClientId = $config['service'];

            $this->assertPurger($purgerId, $elasticSearchClientId, $indexName);

            $this->assertExecutor($executorId, $elasticSearchClientId, $indexName, $purgerId);

            $this->assertLoader($loaderId);

            $this->assertOrchestrator($orchestratorId, $executorId, $purgerId, $loaderId);

            if ($alias === 'alias_3') {
                $this->assertFixturesCommand(
                    $consoleCommandId,
                    $consoleCommandName,
                    LoadElasticsearchFixturesCommand::class,
                    $alias,
                    $orchestratorId,
                    $config['load_command_fixtures_classes_namespace']
                );
            } else {
                $this->assertContainerBuilderNotHasService($consoleCommandId);
            }
        }
    }

    public function testCompileWithoutIndexes(): void
    {
        $this->compile();

        foreach ($this->container->getServiceIds() as $serviceId) {
            $this->assertThatDoesNotMatchRegularExpression('/^kununu_testing\.orchestrator\.elastic_search\.\w+$/m', $serviceId);
            $this->assertThatDoesNotMatchRegularExpression('/^kununu_testing\.orchestrator\.elastic_search\.\w+\.purger$/m', $serviceId);
            $this->assertThatDoesNotMatchRegularExpression('/^kununu_testing\.orchestrator\.elastic_search\.\w+\.executor/m', $serviceId);
            $this->assertThatDoesNotMatchRegularExpression('/^kununu_testing\.orchestrator\.elastic_search\.\w+\.loader/m', $serviceId);
            $this->assertThatDoesNotMatchRegularExpression('/^kununu_testing\.load_fixtures\.elastic_search\.\w+\.command/m', $serviceId);
        }
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ElasticSearchCompilerPass());
    }

    private function assertPurger(string $purgerId, string $elasticSearchClientId, string $indexName): void
    {
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            $purgerId,
            0,
            new Reference($elasticSearchClientId)
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
    }

    private function assertExecutor(string $executorId, string $elasticSearchClient, string $indexName, string $purgerId): void
    {
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            $executorId,
            0,
            new Reference($elasticSearchClient)
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            $executorId,
            1,
            $indexName
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            $executorId,
            2,
            new Reference($purgerId)
        );
        $this->assertContainerBuilderHasService(
            $executorId,
            ElasticSearchExecutor::class
        );
        $this->assertTrue($this->container->getDefinition($executorId)->isPrivate());
    }

    private function assertLoader(string $loaderId): void
    {
        $this->assertContainerBuilderHasService(
            $loaderId,
            ElasticSearchFixturesLoader::class
        );
        $this->assertTrue($this->container->getDefinition($loaderId)->isPrivate());
    }

    private function assertOrchestrator(string $orchestratorId, string $executorId, string $purgerId, string $loaderId): void
    {
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            $orchestratorId,
            0,
            new Reference($executorId)
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            $orchestratorId,
            1,
            new Reference($loaderId)
        );
        $this->assertContainerBuilderHasService(
            $orchestratorId,
            Orchestrator::class
        );
        $this->assertTrue($this->container->getDefinition($orchestratorId)->isPublic());
    }
}
