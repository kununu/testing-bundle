<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Unit\DependencyInjection\Compiler;

use Kununu\DataFixtures\Executor\ElasticsearchExecutor;
use Kununu\DataFixtures\Loader\ElasticsearchFixturesLoader;
use Kununu\DataFixtures\Purger\ElasticsearchPurger;
use Kununu\TestingBundle\Command\LoadElasticsearchFixturesCommand;
use Kununu\TestingBundle\DependencyInjection\Compiler\ElasticsearchCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class ElasticsearchCompilerPassTest extends BaseCompilerPassTestCase
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
                'index_name'                              => 'index1',
                'service'                                 => 'elastic_search_service_2',
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

            $this->assertPurger($purgerId, ElasticsearchPurger::class, new Reference($elasticSearchClientId), $indexName);
            $this->assertExecutor(
                $executorId,
                ElasticsearchExecutor::class,
                new Reference($elasticSearchClientId),
                $indexName,
                new Reference($purgerId)
            );
            $this->assertLoader($loaderId, ElasticsearchFixturesLoader::class);
            $this->assertOrchestrator($orchestratorId, $executorId, $loaderId);

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
            $this->assertDoesNotMatchRegularExpression('/^kununu_testing\.orchestrator\.elastic_search\.\w+$/m', $serviceId);
            $this->assertDoesNotMatchRegularExpression('/^kununu_testing\.orchestrator\.elastic_search\.\w+\.purger$/m', $serviceId);
            $this->assertDoesNotMatchRegularExpression('/^kununu_testing\.orchestrator\.elastic_search\.\w+\.executor/m', $serviceId);
            $this->assertDoesNotMatchRegularExpression('/^kununu_testing\.orchestrator\.elastic_search\.\w+\.loader/m', $serviceId);
            $this->assertDoesNotMatchRegularExpression('/^kununu_testing\.load_fixtures\.elastic_search\.\w+\.command/m', $serviceId);
        }
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ElasticsearchCompilerPass());
    }
}
