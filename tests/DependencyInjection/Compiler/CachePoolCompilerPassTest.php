<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\DependencyInjection\Compiler;

use Kununu\DataFixtures\Executor\CachePoolExecutor;
use Kununu\DataFixtures\Loader\CachePoolFixturesLoader;
use Kununu\DataFixtures\Purger\CachePoolPurger;
use Kununu\TestingBundle\Command\LoadCacheFixturesCommand;
use Kununu\TestingBundle\DependencyInjection\Compiler\CachePoolCompilerPass;
use Kununu\TestingBundle\DependencyInjection\KununuTestingExtension;
use Kununu\TestingBundle\Service\Orchestrator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class CachePoolCompilerPassTest extends BaseCompilerPassTestCase
{
    private const CACHE_POOL_IDS = [
        'cache_pool.service_1'           => [
            'creates_command' => true,
        ],
        'cache_pool.service_2'           => [],
        'cache_pool.service_3.decorated' => [
            'name' => 'cache_pool.service_3',
        ],
    ];

    private const CONFIG = [
        'cache' => [
            'pools' => [
                'cache_pool.service_1' => [
                    'load_command_fixtures_classes_namespace' => [
                        'Kununu\TestingBundle\Tests\App\Fixtures\CachePool\CachePoolFixture1',
                        'Kununu\TestingBundle\Tests\App\Fixtures\CachePool\CachePoolFixture2',
                    ],
                ],
            ],
        ],
    ];

    public function testThatCreatesOrchestratorForEachServiceTaggedAsCachePool(): void
    {
        $this->container->loadFromExtension(KununuTestingExtension::ALIAS, self::CONFIG);

        $this->doAssertionsOnCachePoolsServices(
            function(
                string $purgerId,
                string $executorId,
                string $loaderId,
                string $orchestratorId,
                string $cachePoolId,
                ?string $consoleCommandId,
                ?string $consoleCommandName
            ): void {
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
                    new Reference($loaderId)
                );
                $this->assertContainerBuilderHasService(
                    $orchestratorId,
                    Orchestrator::class
                );
                $this->assertTrue($this->container->getDefinition($orchestratorId)->isPublic());

                if (null !== $consoleCommandId) {
                    $this->assertContainerBuilderHasService($consoleCommandId);

                    $this->assertFixturesCommand(
                        $consoleCommandId,
                        $consoleCommandName,
                        LoadCacheFixturesCommand::class,
                        $cachePoolId,
                        $orchestratorId,
                        self::CONFIG['cache']['pools'][$cachePoolId]['load_command_fixtures_classes_namespace'] ?? []
                    );
                }
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

        foreach (self::CACHE_POOL_IDS as $cachePoolId => $attributes) {
            // It means this cache pool must create a Symfony command to load fixtures to it
            $createsCommand = (bool) ($attributes['creates_command'] ?? false);

            // It means the original definition was decorated. Check CachePoolCompilerPass class for more details.
            if (!empty($attributes['name'])) {
                $cachePoolId = $attributes['name'];
            }

            $purgerId = sprintf('kununu_testing.orchestrator.cache_pools.%s.purger', $cachePoolId);
            $executorId = sprintf('kununu_testing.orchestrator.cache_pools.%s.executor', $cachePoolId);
            $loaderId = sprintf('kununu_testing.orchestrator.cache_pools.%s.loader', $cachePoolId);
            $orchestratorId = sprintf('kununu_testing.orchestrator.cache_pools.%s', $cachePoolId);
            $consoleCommandId = $createsCommand ? sprintf('kununu_testing.load_fixtures.cache_pools.%s.command', $cachePoolId) : null;
            $consoleCommandName = $createsCommand ? sprintf('kununu_testing:load_fixtures:cache_pools:%s', $cachePoolId) : null;

            $asserter($purgerId, $executorId, $loaderId, $orchestratorId, $cachePoolId, $consoleCommandId, $consoleCommandName);
        }
    }
}
