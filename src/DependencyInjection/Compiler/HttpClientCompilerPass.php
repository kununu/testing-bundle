<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\DependencyInjection\Compiler;

use Kununu\DataFixtures\Executor\HttpClientExecutor;
use Kununu\DataFixtures\Loader\HttpClientFixturesLoader;
use Kununu\DataFixtures\Purger\HttpClientPurger;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class HttpClientCompilerPass extends AbstractCompilerPass
{
    private const string CONFIG_KEY = 'http_client';
    private const string CONFIG_CLIENTS = 'clients';
    private const string SERVICE_PREFIX = 'kununu_testing.orchestrator.http_client';

    private array $config = [];

    public function process(ContainerBuilder $container): void
    {
        if (!$this->canBuildOrchestrators($container)) {
            return;
        }

        foreach ($this->config[self::CONFIG_CLIENTS] as $client) {
            $this->buildOrchestrator($container, $client);
        }
    }

    private function canBuildOrchestrators(ContainerBuilder $containerBuilder): bool
    {
        if (null === ($configuration = $this->getExtensionConfiguration($containerBuilder))) {
            return false;
        }

        $this->config = $configuration[self::CONFIG_KEY] ?? [];

        return count($this->config[self::CONFIG_CLIENTS] ?? []) > 0;
    }

    private function buildOrchestrator(ContainerBuilder $container, string $id): void
    {
        $this->registerOrchestrator(
            container: $container,
            baseId: $id,
            // Loader definition for HttpClient with provided id
            loaderId: sprintf('%s.%s.loader', self::SERVICE_PREFIX, $id),
            // Orchestrator definition for HttpClient with provided id
            orchestratorId: sprintf('%s.%s', self::SERVICE_PREFIX, $id),
            // Purger Definition for HttpClient with provided id
            purgerDefinitionBuilder: fn(ContainerBuilder $container, string $baseId): array => [
                sprintf('%s.%s.purger', self::SERVICE_PREFIX, $baseId),
                new Definition(
                    HttpClientPurger::class,
                    [
                        new Reference($id),
                    ]
                ),
            ],
            // Executor Definition for HttpClient with provided id
            executorDefinitionBuilder: fn(ContainerBuilder $container, string $baseId, string $purgerId): array => [
                sprintf('%s.%s.executor', self::SERVICE_PREFIX, $baseId),
                new Definition(
                    HttpClientExecutor::class,
                    [
                        new Reference($id),
                        new Reference($purgerId),
                    ]
                ),
            ],
            loaderClass: HttpClientFixturesLoader::class
        );
    }
}
