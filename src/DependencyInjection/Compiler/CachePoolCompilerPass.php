<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\DependencyInjection\Compiler;

use Kununu\DataFixtures\Executor\CachePoolExecutor;
use Kununu\DataFixtures\Loader\CachePoolFixturesLoader;
use Kununu\DataFixtures\Purger\CachePoolPurger;
use Kununu\TestingBundle\Command\LoadCacheFixturesCommand;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class CachePoolCompilerPass extends AbstractLoadFixturesCommandCompilerPass
{
    private const string CONFIG_KEY = 'cache';
    private const string CONFIG_ENABLE = 'enable';
    private const string CONFIG_POOLS = 'pools';
    private const string CACHE_POOL_TAG = 'cache.pool';
    private const string NAME_KEY = 'name';

    private array $config = [];

    public function process(ContainerBuilder $container): void
    {
        if (!$this->canBuildOrchestrators($container)) {
            return;
        }

        foreach ($this->getOrchestratorsIds($container, $container->findTaggedServiceIds(self::CACHE_POOL_TAG)) as $id) {
            $this->buildContainerDefinitions($container, $id);
        }
    }

    protected function getSectionName(): string
    {
        return 'cache_pools';
    }

    protected function getCommandClass(): string
    {
        return LoadCacheFixturesCommand::class;
    }

    protected function getPurgerClass(): string
    {
        return CachePoolPurger::class;
    }

    protected function getExecutorClass(): string
    {
        return CachePoolExecutor::class;
    }

    protected function getLoaderClass(): string
    {
        return CachePoolFixturesLoader::class;
    }

    private function canBuildOrchestrators(ContainerBuilder $containerBuilder): bool
    {
        if (null === ($configuration = $this->getExtensionConfiguration($containerBuilder))) {
            return false;
        }

        $this->config = $configuration[self::CONFIG_KEY] ?? [];

        return (bool) ($this->config[self::CONFIG_ENABLE] ?? true);
    }

    private function getOrchestratorsIds(ContainerBuilder $containerBuilder, array $cachePoolServices): array
    {
        $ids = [];
        foreach ($cachePoolServices as $id => $tags) {
            $definition = $containerBuilder->getDefinition($id);

            if (!$definition->isAbstract()) {
                // Cache Pools can be decorated.
                // For example, when using the tags option, the cache pool adapter is decorated.
                //
                // In this case the attributes of this tag contain the original name of the cache pool
                // So we need to rely on those names and give aliases using this names to the $id
                $attributes = $definition->getTag(self::CACHE_POOL_TAG);

                $isDecorated = false;

                foreach ($attributes as $attribute) {
                    if (isset($attribute[self::NAME_KEY]) && !empty($name = $attribute[self::NAME_KEY])) {
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
        $orchestratorId = $this->buildOrchestrator($containerBuilder, $id);

        // Only build load fixture commands for configured cache pools
        if (!isset($this->config[self::CONFIG_POOLS][$id])) {
            return;
        }

        $this->buildLoadFixturesCommand(
            container: $containerBuilder,
            fixtureType: $this->sectionName,
            orchestratorId: $orchestratorId,
            commandClassName: $this->commandClass,
            name: $id,
            namespace: $this->config[self::CONFIG_POOLS][$id][self::LOAD_COMMAND_CLASSES_NAMESPACE_CONFIG] ?? []
        );
    }

    private function buildOrchestrator(ContainerBuilder $containerBuilder, string $id): string
    {
        $this->buildGenericOrchestrator(
            container: $containerBuilder,
            baseId: $id,
            // Loader Definition for the CachePool with provided id
            loaderId: sprintf('%s.%s.loader', $this->orchestratorServicePrefix, $id),
            // Orchestrator Definition for the CachePool with provided id
            orchestratorId: $orchestratorId = sprintf('%s.%s', $this->orchestratorServicePrefix, $id),
            // Purger Definition for the CachePool with provided id
            purgerDefinitionBuilder: fn(ContainerBuilder $container, string $baseId): array => [
                sprintf('%s.%s.purger', $this->orchestratorServicePrefix, $id),
                new Definition(
                    $this->purgerClass,
                    [
                        new Reference($baseId),
                    ]
                ),
            ],
            // Executor Definition for the CachePool with provided id
            executorDefinitionBuilder: fn(ContainerBuilder $container, string $baseId, string $purgerId): array => [
                sprintf('%s.%s.executor', $this->orchestratorServicePrefix, $id),
                new Definition(
                    $this->executorClass,
                    [
                        new Reference($baseId),
                        new Reference($purgerId),
                    ]
                ),
            ],
        );

        return $orchestratorId;
    }
}
