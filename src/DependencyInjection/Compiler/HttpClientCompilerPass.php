<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\DependencyInjection\Compiler;

use Kununu\DataFixtures\Executor\HttpClientExecutor;
use Kununu\DataFixtures\Loader\HttpClientFixturesLoader;
use Kununu\DataFixtures\Purger\HttpClientPurger;
use Kununu\TestingBundle\Service\Orchestrator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class HttpClientCompilerPass extends AbstractCompilerPass
{
    private const SERVICE_PREFIX = 'kununu_testing.orchestrator.http_client';

    private array $config = [];

    public function process(ContainerBuilder $container): void
    {
        if (!$this->canBuildOrchestrators($container)) {
            return;
        }

        foreach ($this->config['clients'] as $client) {
            $this->buildHttpClientOrchestrator($container, $client);
        }
    }

    private function canBuildOrchestrators(ContainerBuilder $containerBuilder): bool
    {
        if (null === ($configuration = $this->getExtensionConfiguration($containerBuilder))) {
            return false;
        }

        $this->config = $configuration['http_client'] ?? [];

        return count($this->config['clients'] ?? []) > 0;
    }

    private function buildHttpClientOrchestrator(ContainerBuilder $container, string $id): void
    {
        // Purger Definition for HttpClient with provided $id
        $purgerId = sprintf('%s.%s.purger', self::SERVICE_PREFIX, $id);
        $purgerDefinition = new Definition(
            HttpClientPurger::class,
            [
                $httpClient = new Reference($id),
            ]
        );
        $container->setDefinition($purgerId, $purgerDefinition);

        // Executor Definition for HttpClient with provided $id
        $executorId = sprintf('%s.%s.executor', self::SERVICE_PREFIX, $id);
        $executorDefinition = new Definition(HttpClientExecutor::class, [$httpClient, new Reference($purgerId)]);
        $container->setDefinition($executorId, $executorDefinition);

        // Loader definition for HttpClient with provided $id
        $loaderId = sprintf('%s.%s.loader', self::SERVICE_PREFIX, $id);
        $loaderDefinition = new Definition(HttpClientFixturesLoader::class);
        $container->setDefinition($loaderId, $loaderDefinition);

        // Orchestrator definition for HttpClient with provided $id
        $orchestratorDefinition = new Definition(
            Orchestrator::class,
            [
                new Reference($executorId),
                new Reference($loaderId),
            ]
        );
        $orchestratorDefinition->setPublic(true);

        $orchestratorId = sprintf('%s.%s', self::SERVICE_PREFIX, $id);

        $container->setDefinition($orchestratorId, $orchestratorDefinition);
    }
}
