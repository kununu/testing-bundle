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
    private const SERVICE_PREFIX = 'kununu_testing.orchestrator.cache_pools';

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
        $purgerId = sprintf('%s.%s.purger',self::SERVICE_PREFIX, $id);
        $purgerDefinition = new Definition(CachePoolPurger::class, [$cachePool]);
        $container->setDefinition($purgerId, $purgerDefinition);

        // Executor Definition for the CachePool with provided $id
        $executorId = sprintf('%s.%s.executor',self::SERVICE_PREFIX, $id);
        $executorDefinition = new Definition(CachePoolExecutor::class, [$cachePool, new Reference($purgerId)]);
        $container->setDefinition($executorId, $executorDefinition);

        // Loader Definition for the CachePool with provided $id
        $loaderId = sprintf('%s.%s.loader',self::SERVICE_PREFIX, $id);
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

        $container->setDefinition(sprintf('%s.%s', self::SERVICE_PREFIX, $id), $cachePoolOrchestratorDefinition);
    }
}
