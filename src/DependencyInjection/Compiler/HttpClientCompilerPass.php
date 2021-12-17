<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\DependencyInjection\Compiler;

use Kununu\DataFixtures\Executor\HttpClientExecutor;
use Kununu\DataFixtures\Loader\HttpClientFixturesLoader;
use Kununu\DataFixtures\Purger\HttpClientPurger;
use Kununu\TestingBundle\DependencyInjection\ExtensionConfiguration;
use Kununu\TestingBundle\DependencyInjection\KununuTestingExtension;
use Kununu\TestingBundle\Service\Orchestrator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class HttpClientCompilerPass implements CompilerPassInterface
{
    private const SERVICE_PREFIX = 'kununu_testing.orchestrator.http_client';

    private $config;

    public function process(ContainerBuilder $container): void
    {
        if (!$this->canBuildOrchestrators($container)) {
            return;
        }

        foreach ($this->config['clients'] as $client) {
            $this->buildHttpClientOrchestrator($container, $client, $this->config);
        }
    }

    private function canBuildOrchestrators(ContainerBuilder $containerBuilder): bool
    {
        if (!$containerBuilder->hasExtension(KununuTestingExtension::ALIAS) ||
            !($extension = $containerBuilder->getExtension(KununuTestingExtension::ALIAS)) instanceof ExtensionConfiguration
        ) {
            return false;
        }

        $this->config = $extension->getConfig()['http_client'] ?? [];

        return count($this->config['clients'] ?? []) > 0;
    }

    private function buildHttpClientOrchestrator(ContainerBuilder $container, string $id): void
    {
        $httpClient = new Reference($id);

        // Purger Definition
        $purgerId = sprintf('%s.%s.purger', self::SERVICE_PREFIX, $id);
        $purgerDefinition = new Definition(HttpClientPurger::class, [$httpClient]);
        $container->setDefinition($purgerId, $purgerDefinition);

        // Executor Definition
        $executorId = sprintf('%s.%s.executor', self::SERVICE_PREFIX, $id);
        $executorDefinition = new Definition(HttpClientExecutor::class, [$httpClient, new Reference($purgerId)]);
        $container->setDefinition($executorId, $executorDefinition);

        // Loader definition
        $loaderId = sprintf('%s.%s.loader', self::SERVICE_PREFIX, $id);
        $loaderDefinition = new Definition(HttpClientFixturesLoader::class);
        $container->setDefinition($loaderId, $loaderDefinition);

        $connectionOrchestratorDefinition = new Definition(
            Orchestrator::class,
            [
                new Reference($executorId),
                new Reference($loaderId),
            ]
        );
        $connectionOrchestratorDefinition->setPublic(true);

        $orchestratorId = sprintf('%s.%s', self::SERVICE_PREFIX, $id);

        $container->setDefinition($orchestratorId, $connectionOrchestratorDefinition);
    }
}
