<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Test;

use Kununu\TestingBundle\Service\Orchestrator;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

abstract class FixturesAwareTestCase extends BaseWebTestCase
{
    private const KEY_CONNECTIONS = 'connections';
    private const KEY_CACHE_POOLS = 'cache_pools';
    private const KEY_ELASTICSEARCH = 'elastic_search';

    final protected function loadDbFixtures(
        string $connectionName,
        array $classNames = [],
        bool $append = false,
        bool $clearFixtures = true
    ): void {
        $this->getOrchestrator(self::KEY_CONNECTIONS, $connectionName)->execute($classNames, $append, $clearFixtures);
    }

    final protected function loadCachePoolFixtures(
        string $cachePoolServiceId,
        array $classNames = [],
        bool $append = false,
        bool $clearFixtures = true
    ): void {
        $this->getOrchestrator(self::KEY_CACHE_POOLS, $cachePoolServiceId)->execute($classNames, $append, $clearFixtures);
    }

    final protected function loadElasticSearchFixtures(
        string $alias,
        array $classNames = [],
        bool $append = false,
        bool $clearFixtures = true
    ): void {
        $this->getOrchestrator(self::KEY_ELASTICSEARCH, $alias)->execute($classNames, $append, $clearFixtures);
    }

    final protected function registerInitializableFixtureForDb(
        string $connectionName,
        string $className,
        ...$args
    ): void {
        $this->getOrchestrator(self::KEY_CONNECTIONS, $connectionName)->registerInitializableFixture($className, ...$args);
    }

    final protected function registerInitializableFixtureForCachePool(
        string $cachePoolServiceId,
        string $className,
        ...$args
    ): void {
        $this->getOrchestrator(self::KEY_CACHE_POOLS, $cachePoolServiceId)->registerInitializableFixture($className, ...$args);
    }

    final protected function registerInitializableFixtureForElasticSearch(
        string $alias,
        string $className,
        ...$args
    ): void {
        $this->getOrchestrator(self::KEY_ELASTICSEARCH, $alias)->registerInitializableFixture($className, ...$args);
    }

    final protected function getFixturesContainer(): ContainerInterface
    {
        if (!static::$kernel || !(method_exists(static::class, 'getContainer') ? static::getContainer() : static::$container)) {
            static::createClient();
        }

        return method_exists(static::class, 'getContainer') ? static::getContainer() : static::$container;
    }

    /**
     * @codeCoverageIgnore
     */
    final protected function clearDbFixtures(string $connectionName): self
    {
        $this->getOrchestrator(self::KEY_CONNECTIONS, $connectionName)->clearFixtures();

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    final protected function clearCachePoolFixtures(string $cachePoolServiceId): self
    {
        $this->getOrchestrator(self::KEY_CACHE_POOLS, $cachePoolServiceId)->clearFixtures();

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    final protected function clearElasticSearchFixtures(string $alias): self
    {
        $this->getOrchestrator(self::KEY_ELASTICSEARCH, $alias)->clearFixtures();

        return $this;
    }

    private function getOrchestrator(string $type, string $key): Orchestrator
    {
        return $this->getFixturesContainer()->get(sprintf('kununu_testing.orchestrator.%s.%s', $type, $key));
    }
}
