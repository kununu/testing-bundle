<?php declare(strict_types=1);

namespace Kununu\TestingBundle\Test;

use Kununu\TestingBundle\Service\Orchestrator;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

abstract class FixturesAwareTestCase extends BaseWebTestCase
{
    final protected function loadDbFixtures(string $connectionName, array $classNames = [], bool $append = false): void
    {
        $this->getOrchestrator('connections', $connectionName)->execute($classNames, $append);
    }

    final protected function loadCachePoolFixtures(
        string $cachePoolServiceId,
        array $classNames = [],
        bool $append = false
    ): void {
        $this->getOrchestrator('cache_pools', $cachePoolServiceId)->execute($classNames, $append);
    }

    final protected function loadElasticSearchFixtures(
        string $alias,
        array $classNames = [],
        bool $append = false
    ): void {
        $this->getOrchestrator('elastic_search', $alias)->execute($classNames, $append);
    }

    final protected function registerInitializableFixtureForDb(
        string $connectionName,
        string $className,
        ...$args
    ): void {
        $this->getOrchestrator('connections', $connectionName)->registerInitializableFixture($className, ...$args);
    }

    final protected function registerInitializableFixtureForCachePool(
        string $cachePoolServiceId,
        string $className,
        ...$args
    ): void {
        $this->getOrchestrator('cache_pools', $cachePoolServiceId)->registerInitializableFixture($className, ...$args);
    }

    final protected function registerInitializableFixtureForElasticSearch(
        string $alias,
        string $className,
        ...$args
    ): void {
        $this->getOrchestrator('elastic_search', $alias)->registerInitializableFixture($className, ...$args);
    }

    final protected function getContainer(): ContainerInterface
    {
        if (!static::$kernel || !static::$container) {
            static::createClient();
        }

        return static::$container;
    }

    private function getOrchestrator(string $type, string $key): Orchestrator
    {
        /** @var Orchestrator $orchestrator */
        $orchestrator = $this->getContainer()->get(sprintf('kununu_testing.orchestrator.%s.%s', $type, $key));

        return $orchestrator;
    }
}
