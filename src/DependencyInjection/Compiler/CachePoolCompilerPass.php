<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\DependencyInjection\Compiler;

use Kununu\DataFixtures\Executor\CachePoolExecutor;
use Kununu\DataFixtures\Loader\CachePoolFixturesLoader;
use Kununu\DataFixtures\Purger\CachePoolPurger;
use Kununu\TestingBundle\Command\LoadCacheFixturesCommand;
use Kununu\TestingBundle\DependencyInjection\ExtensionConfiguration;
use Kununu\TestingBundle\DependencyInjection\KununuTestingExtension;
use Kununu\TestingBundle\Service\Orchestrator;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class CachePoolCompilerPass implements CompilerPassInterface
{
    use LoadFixturesCommandsTrait;

    private const SERVICE_PREFIX = 'kununu_testing.orchestrator.cache_pools';
    private const CACHE_POOL_TAG = 'cache.pool';

    private $config;

    public function process(ContainerBuilder $container): void
    {
        if (!$this->canBuildOrchestrators($container)) {
            return;
        }

        foreach ($this->getOrchestratorsIds($container, $container->findTaggedServiceIds(self::CACHE_POOL_TAG)) as $id) {
            $this->buildContainerDefinitions($container, $id);
        }
    }

    private function canBuildOrchestrators(ContainerBuilder $containerBuilder): bool
    {
        if (!$containerBuilder->hasExtension(KununuTestingExtension::ALIAS) ||
            !($extension = $containerBuilder->getExtension(KununuTestingExtension::ALIAS)) instanceof ExtensionConfiguration
        ) {
            return false;
        }

        $this->config = $extension->getConfig()['cache'] ?? [];

        return (bool) ($this->config['enable'] ?? true);
    }

    private function getOrchestratorsIds(ContainerBuilder $containerBuilder, array $cachePoolServices): array
    {
        $ids = [];
        foreach ($cachePoolServices as $id => $tags) {
            $definition = $containerBuilder->getDefinition($id);

            if (!$definition->isAbstract()) {
                // Cache Pools can be decorated. For example, when using the tags option, the cache pool adapter is decorated.
                // In this case the attributes of this tag contain the original name of the cache pool
                // So we need to rely on those names and give aliases using this names to the $id
                $attributes = $definition->getTag(self::CACHE_POOL_TAG);

                $isDecorated = false;

                foreach ($attributes as $attribute) {
                    if (isset($attribute['name']) && !empty($name = $attribute['name'])) {
                        $ids[] = $name;
                        $isDecorated = true;
                    }
                }

                if (!$isDecorated) {
                    $ids[] = $id;
                }
            }
        }

        return $ids;
    }

    private function buildContainerDefinitions(ContainerBuilder $containerBuilder, string $id): void
    {
        $orchestratorId = $this->buildCachePoolOrchestrator($containerBuilder, $id);

        if (!isset($this->config['pools'][$id])) {
            return;
        }

        $this->buildLoadFixturesCommand(
            $containerBuilder,
            'cache_pools',
            $orchestratorId,
            LoadCacheFixturesCommand::class,
            $id,
            $this->config['pools'][$id]['load_command_fixtures_classes_namespace'] ?? []
        );
    }

    private function buildCachePoolOrchestrator(ContainerBuilder $containerBuilder, string $id): string
    {
        /** @var CacheItemPoolInterface $cachePool */
        $cachePool = new Reference($id);

        // Purger Definition for the CachePool with provided $id
        $purgerId = sprintf('%s.%s.purger', self::SERVICE_PREFIX, $id);
        $purgerDefinition = new Definition(CachePoolPurger::class, [$cachePool]);
        $containerBuilder->setDefinition($purgerId, $purgerDefinition);

        // Executor Definition for the CachePool with provided $id
        $executorId = sprintf('%s.%s.executor', self::SERVICE_PREFIX, $id);
        $executorDefinition = new Definition(CachePoolExecutor::class, [$cachePool, new Reference($purgerId)]);
        $containerBuilder->setDefinition($executorId, $executorDefinition);

        // Loader Definition for the CachePool with provided $id
        $loaderId = sprintf('%s.%s.loader', self::SERVICE_PREFIX, $id);
        $loaderDefinition = new Definition(CachePoolFixturesLoader::class);
        $containerBuilder->setDefinition($loaderId, $loaderDefinition);

        $cachePoolOrchestratorDefinition = new Definition(
            Orchestrator::class,
            [
                new Reference($executorId),
                new Reference($loaderId),
            ]
        );
        $cachePoolOrchestratorDefinition->setPublic(true);

        $orchestratorId = sprintf('%s.%s', self::SERVICE_PREFIX, $id);
        $containerBuilder->setDefinition($orchestratorId, $cachePoolOrchestratorDefinition);

        return $orchestratorId;
    }
}
