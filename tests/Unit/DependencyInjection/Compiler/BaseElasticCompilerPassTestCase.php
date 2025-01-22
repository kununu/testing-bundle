<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Unit\DependencyInjection\Compiler;

use Kununu\TestingBundle\DependencyInjection\Compiler\AbstractElasticCompilerPass;
use Symfony\Component\DependencyInjection\Reference;

abstract class BaseElasticCompilerPassTestCase extends BaseLoadFixturesCommandCompilerPassTestCase
{
    public function testCompile(): void
    {
        $section = $this->getSectionName();
        $indices = $this->getIndices($section);

        $this->setParameter(sprintf('kununu_testing.%s', $section), $indices);

        $this->compile();

        foreach ($indices as $alias => $config) {
            $purgerId = sprintf('kununu_testing.orchestrator.%s.%s.purger', $section, $alias);
            $executorId = sprintf('kununu_testing.orchestrator.%s.%s.executor', $section, $alias);
            $loaderId = sprintf('kununu_testing.orchestrator.%s.%s.loader', $section, $alias);
            $orchestratorId = sprintf('kununu_testing.orchestrator.%s.%s', $section, $alias);
            $consoleCommandId = sprintf('kununu_testing.load_fixtures.%s.%s.command', $section, $alias);
            $consoleCommandName = sprintf('kununu_testing:load_fixtures:%s:%s', $section, $alias);

            $indexName = $config['index_name'];
            $clientId = $config['service'];

            $this->assertPurger($purgerId, $this->getPurgerClass(), new Reference($clientId), $indexName);
            $this->assertExecutor(
                $executorId,
                $this->getExecutorClass(),
                new Reference($clientId),
                $indexName,
                new Reference($purgerId)
            );
            $this->assertLoader($loaderId, $this->getLoaderClass());
            $this->assertOrchestrator($orchestratorId, $executorId, $loaderId);

            if ($alias === 'alias_3') {
                $this->assertFixturesCommand(
                    $consoleCommandId,
                    $consoleCommandName,
                    $this->getCommandClass(),
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
        $section = $this->getSectionName();

        $this->compile();

        foreach ($this->container->getServiceIds() as $serviceId) {
            self::assertDoesNotMatchRegularExpression(
                sprintf('/^kununu_testing\.orchestrator\.%s\.\w+$/m', $section),
                $serviceId
            );
            self::assertDoesNotMatchRegularExpression(
                sprintf('/^kununu_testing\.orchestrator\.%s\.\w+\.purger$/m', $section),
                $serviceId
            );
            self::assertDoesNotMatchRegularExpression(
                sprintf('/^kununu_testing\.orchestrator\.%s\.\w+\.executor/m', $section),
                $serviceId
            );
            self::assertDoesNotMatchRegularExpression(
                sprintf('/^kununu_testing\.orchestrator\.%s\.\w+\.loader/m', $section),
                $serviceId
            );
            self::assertDoesNotMatchRegularExpression(
                sprintf('/^kununu_testing\.load_fixtures\.%s\.\w+\.command/m', $section),
                $serviceId
            );
        }
    }

    abstract protected function getCompilerInstance(): AbstractElasticCompilerPass;

    private function getIndices(string $section): array
    {
        return [
            'alias_1' => [
                'index_name' => 'index1',
                'service'    => sprintf('%s_service_1', $section),
            ],
            'alias_2' => [
                'load_command_fixtures_classes_namespace' => [],
                'index_name'                              => 'index2',
                'service'                                 => sprintf('%s_service_1', $section),
            ],
            'alias_3' => [
                'load_command_fixtures_classes_namespace' => [
                    'App/DataFixtures/Fixture1',
                    'App/DataFixtures/Fixture2',
                ],
                'index_name'                              => 'index1',
                'service'                                 => sprintf('%s_service_2', $section),
            ],
        ];
    }
}
