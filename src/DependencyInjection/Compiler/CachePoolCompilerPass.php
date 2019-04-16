<?php declare(strict_types=1);

namespace Kununu\TestingBundle\DependencyInjection\Compiler;

use Kununu\DataFixtures\Executor\CachePoolExecutor;
use Kununu\DataFixtures\Loader\CachePoolFixturesLoader;
use Kununu\DataFixtures\Purger\CachePoolPurger;
use Kununu\TestingBundle\Service\Orchestrator;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class CachePoolCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $cachePoolServices = $container->findTaggedServiceIds('cache.pool');

        foreach ($cachePoolServices as $id => $tags) {
            $definition = $container->getDefinition($id);

            if (!$definition->isAbstract()) {
                $this->buildCachePoolOrchestrator($container, $id);
            }
        }
    }

    private function buildCachePoolOrchestrator(ContainerBuilder $container, string $id) : void
    {
        /** @var CacheItemPoolInterface $cachePool */
        $cachePool = new Reference($id);

        // Purger Definition for the CachePool with provided $id
        $purgerId = sprintf('kununu_testing.orchestrator.cache_pools.%s.purger', $id);
        $purgerDefinition = new Definition(CachePoolPurger::class, [$cachePool]);
        $container->setDefinition($purgerId, $purgerDefinition);

        // Executor Definition for the CachePool with provided $id
        $executorId = sprintf('kununu_testing.orchestrator.cache_pools.%s.executor', $id);
        $executorDefinition = new Definition(CachePoolExecutor::class, [$cachePool, new Reference($purgerId)]);
        $container->setDefinition($executorId, $executorDefinition);

        // Loader Definition for the CachePool with provided $id
        $loaderId = sprintf('kununu_testing.orchestrator.cache_pools.%s.loader', $id);
        $loaderDefinition = new Definition(CachePoolFixturesLoader::class);
        $container->setDefinition($loaderId, $loaderDefinition);

        $cachePoolOrchestratorDefinition = new Definition(
            Orchestrator::class,
            [
                new Reference($executorId),
                new Reference($purgerId),
                new Reference($loaderId),
            ]
        );
        $cachePoolOrchestratorDefinition->setPublic(true);

        $container->setDefinition(sprintf('kununu_testing.orchestrator.cache_pools.%s', $id), $cachePoolOrchestratorDefinition);
    }
}
