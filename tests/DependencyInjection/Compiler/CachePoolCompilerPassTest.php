<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\DependencyInjection\Compiler;

use Kununu\DataFixtures\Executor\CachePoolExecutor;
use Kununu\DataFixtures\Loader\CachePoolFixturesLoader;
use Kununu\DataFixtures\Purger\CachePoolPurger;
use Kununu\TestingBundle\DependencyInjection\Compiler\CachePoolCompilerPass;
use Kununu\TestingBundle\DependencyInjection\KununuTestingExtension;
use Kununu\TestingBundle\Service\Orchestrator;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class CachePoolCompilerPassTest extends AbstractCompilerPassTestCase
{
    private const CACHE_POOL_IDS = [
        'cache_pool.service_1'           => [],
        'cache_pool.service_2'           => [],
        'cache_pool.service_3.decorated' => [
            'name' => 'cache_pool.service_3',
        ],
    ];

    public function testThatCreatesOrchestratorForEachServiceTaggedAsCachePool(): void
    {
        $this->doAssertionsOnCachePoolsServices(
            function(string $purgerId, string $executorId, string $loaderId, string $orchestratorId, string $cachePoolId): void {
                $this->assertContainerBuilderHasServiceDefinitionWithArgument(
                    $purgerId,
                    0,
                    new Reference($cachePoolId)
                );
                $this->assertContainerBuilderHasService(
                    $purgerId,
                    CachePoolPurger::class
                );
                $this->assertTrue($this->container->getDefinition($purgerId)->isPrivate());

                $this->assertContainerBuilderHasServiceDefinitionWithArgument(
                    $executorId,
                    0,
                    new Reference($cachePoolId)
                );
                $this->assertContainerBuilderHasServiceDefinitionWithArgument(
                    $executorId,
                    1,
                    new Reference($purgerId)
                );
                $this->assertContainerBuilderHasService(
                    $executorId,
                    CachePoolExecutor::class
                );
                $this->assertTrue($this->container->getDefinition($executorId)->isPrivate());

                $this->assertContainerBuilderHasService(
                    $loaderId,
                    CachePoolFixturesLoader::class
                );
                $this->assertTrue($this->container->getDefinition($loaderId)->isPrivate());

                $this->assertContainerBuilderHasServiceDefinitionWithArgument(
                    $orchestratorId,
                    0,
                    new Reference($executorId)
                );
                $this->assertContainerBuilderHasServiceDefinitionWithArgument(
                    $orchestratorId,
                    1,
                    new Reference($purgerId)
                );
                $this->assertContainerBuilderHasServiceDefinitionWithArgument(
                    $orchestratorId,
                    2,
                    new Reference($loaderId)
                );
                $this->assertContainerBuilderHasService(
                    $orchestratorId,
                    Orchestrator::class
                );
                $this->assertTrue($this->container->getDefinition($orchestratorId)->isPublic());
            }
        );
    }

    public function testThatCreatesOrchestratorForEachServiceTaggedAsCachePoolIsNotCalled(): void
    {
        $this->container->loadFromExtension(KununuTestingExtension::ALIAS, ['cache' => ['enable' => false]]);
        $this->doAssertionsOnCachePoolsServices(
            function(string $purgerId, string $executorId, string $loaderId, string $orchestratorId): void {
                $this->assertContainerBuilderNotHasService($purgerId);
                $this->assertContainerBuilderNotHasService($executorId);
                $this->assertContainerBuilderNotHasService($loaderId);
                $this->assertContainerBuilderNotHasService($orchestratorId);
            }
        );
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new CachePoolCompilerPass());
        $container->registerExtension(new KununuTestingExtension());

        foreach (self::CACHE_POOL_IDS as $cachePoolId => $tagAttributes) {
            $cachePoolDefinition = new Definition();
            $cachePoolDefinition->addTag('cache.pool', $tagAttributes);
            $this->setDefinition($cachePoolId, $cachePoolDefinition);
        }
    }

    private function doAssertionsOnCachePoolsServices(callable $asserter): void
    {
        $this->compile();

        foreach (self::CACHE_POOL_IDS as $cachePoolId => $tagAttributes) {
            // It means the original definition was decorated. Check CachePoolCompilerPass class for more details.
            if (!empty($tagAttributes['name'])) {
                $cachePoolId = $tagAttributes['name'];
            }

            $purgerId = sprintf('kununu_testing.orchestrator.cache_pools.%s.purger', $cachePoolId);
            $executorId = sprintf('kununu_testing.orchestrator.cache_pools.%s.executor', $cachePoolId);
            $loaderId = sprintf('kununu_testing.orchestrator.cache_pools.%s.loader', $cachePoolId);
            $orchestratorId = sprintf('kununu_testing.orchestrator.cache_pools.%s', $cachePoolId);

            $asserter($purgerId, $executorId, $loaderId, $orchestratorId, $cachePoolId);
        }
    }
}
