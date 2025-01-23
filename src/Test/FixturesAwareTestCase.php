<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Test;

use Kununu\TestingBundle\Service\Orchestrator;
use Kununu\TestingBundle\Test\Options\DbOptionsInterface;
use Kununu\TestingBundle\Test\Options\OptionsInterface;

abstract class FixturesAwareTestCase extends AbstractTestCase
{
    private const string KEY_CONNECTIONS = 'connections';
    private const string KEY_NON_TRANSACTIONAL_CONNECTIONS = 'non_transactional_connections';
    private const string KEY_CACHE_POOLS = 'cache_pools';
    private const string KEY_ELASTICSEARCH = 'elastic_search';
    private const string KEY_OPEN_SEARCH = 'open_search';
    private const string KEY_HTTP_CLIENT = 'http_client';

    final protected function clearCachePoolFixtures(string $cachePoolServiceId): static
    {
        return $this->clearFixtures(self::KEY_CACHE_POOLS, $cachePoolServiceId);
    }

    final protected function clearDbFixtures(string $connectionName, DbOptionsInterface $options): static
    {
        return $this->clearFixtures(
            $options->transactional() ? self::KEY_CONNECTIONS : self::KEY_NON_TRANSACTIONAL_CONNECTIONS,
            $connectionName
        );
    }

    final protected function clearElasticsearchFixtures(string $alias): static
    {
        return $this->clearFixtures(self::KEY_ELASTICSEARCH, $alias);
    }

    final protected function clearHttpClientFixtures(string $httpClientServiceId): static
    {
        return $this->clearFixtures(self::KEY_HTTP_CLIENT, $httpClientServiceId);
    }

    final protected function clearOpenSearchFixtures(string $alias): static
    {
        return $this->clearFixtures(self::KEY_OPEN_SEARCH, $alias);
    }

    final protected function getCachePoolFixtures(string $cachePoolServiceId): array
    {
        return $this->getFixtures(self::KEY_CACHE_POOLS, $cachePoolServiceId);
    }

    final protected function getDbFixtures(string $connectionName, DbOptionsInterface $options): array
    {
        return $this->getFixtures(
            $options->transactional() ? self::KEY_CONNECTIONS : self::KEY_NON_TRANSACTIONAL_CONNECTIONS,
            $connectionName
        );
    }

    final protected function getElasticsearchFixtures(string $alias): array
    {
        return $this->getFixtures(self::KEY_ELASTICSEARCH, $alias);
    }

    final protected function getHttpClientFixtures(string $httpClientServiceId): array
    {
        return $this->getFixtures(self::KEY_HTTP_CLIENT, $httpClientServiceId);
    }

    final protected function getOpenSearchFixtures(string $alias): array
    {
        return $this->getFixtures(self::KEY_ELASTICSEARCH, $alias);
    }

    final protected function loadCachePoolFixtures(
        string $cachePoolServiceId,
        OptionsInterface $options,
        string ...$classNames,
    ): void {
        $this->loadFixtures(self::KEY_CACHE_POOLS, $cachePoolServiceId, $options, ...$classNames);
    }

    final protected function loadDbFixtures(
        string $connectionName,
        DbOptionsInterface $options,
        string ...$classNames,
    ): void {
        $this->loadFixtures(
            $options->transactional() ? self::KEY_CONNECTIONS : self::KEY_NON_TRANSACTIONAL_CONNECTIONS,
            $connectionName,
            $options,
            ...$classNames
        );
    }

    final protected function loadElasticsearchFixtures(
        string $alias,
        OptionsInterface $options,
        string ...$classNames,
    ): void {
        $this->loadFixtures(self::KEY_ELASTICSEARCH, $alias, $options, ...$classNames);
    }

    final protected function loadHttpClientFixtures(
        string $httpClientServiceId,
        OptionsInterface $options,
        string ...$classNames,
    ): void {
        $this->loadFixtures(self::KEY_HTTP_CLIENT, $httpClientServiceId, $options, ...$classNames);
    }

    final protected function loadOpenSearchFixtures(
        string $alias,
        OptionsInterface $options,
        string ...$classNames,
    ): void {
        $this->loadFixtures(self::KEY_OPEN_SEARCH, $alias, $options, ...$classNames);
    }

    final protected function registerInitializableFixtureForCachePool(
        string $cachePoolServiceId,
        string $className,
        mixed ...$args,
    ): void {
        $this->registerInitializableFixture(self::KEY_CACHE_POOLS, $cachePoolServiceId, $className, ...$args);
    }

    final protected function registerInitializableFixtureForDb(
        string $connectionName,
        string $className,
        mixed ...$args,
    ): void {
        $this->registerInitializableFixture(self::KEY_CONNECTIONS, $connectionName, $className, ...$args);
    }

    final protected function registerInitializableFixtureForElasticsearch(
        string $alias,
        string $className,
        mixed ...$args,
    ): void {
        $this->registerInitializableFixture(self::KEY_ELASTICSEARCH, $alias, $className, ...$args);
    }

    final protected function registerInitializableFixtureForHttpClient(
        string $httpClientServiceId,
        string $className,
        mixed ...$args,
    ): void {
        $this->registerInitializableFixture(self::KEY_HTTP_CLIENT, $httpClientServiceId, $className, ...$args);
    }

    final protected function registerInitializableFixtureForNonTransactionalDb(
        string $connectionName,
        string $className,
        mixed ...$args,
    ): void {
        $this->registerInitializableFixture(
            self::KEY_NON_TRANSACTIONAL_CONNECTIONS,
            $connectionName,
            $className,
            ...$args
        );
    }

    final protected function registerInitializableFixtureForOpenSearch(
        string $alias,
        string $className,
        mixed ...$args,
    ): void {
        $this->registerInitializableFixture(self::KEY_OPEN_SEARCH, $alias, $className, ...$args);
    }

    private function clearFixtures(string $type, string $key): static
    {
        $this->getOrchestrator($type, $key)->clearFixtures();

        return $this;
    }

    private function getFixtures(string $type, string $key): array
    {
        return $this->getOrchestrator($type, $key)->getFixtures();
    }

    private function loadFixtures(string $type, string $key, OptionsInterface $options, string ...$classNames): void
    {
        $this
            ->getOrchestrator($type, $key)
            ->execute($classNames, $options->append(), $options->clear());
    }

    private function registerInitializableFixture(string $type, string $key, string $className, mixed ...$args): void
    {
        $this
            ->getOrchestrator($type, $key)
            ->registerInitializableFixture($className, ...$args);
    }

    private function getOrchestrator(string $type, string $key): Orchestrator
    {
        $orchestrator = $this->getServiceFromContainer(sprintf('kununu_testing.orchestrator.%s.%s', $type, $key));
        assert($orchestrator instanceof Orchestrator);

        return $orchestrator;
    }
}
