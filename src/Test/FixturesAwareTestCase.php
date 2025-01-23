<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Test;

use Kununu\TestingBundle\Service\Orchestrator;
use Kununu\TestingBundle\Test\Options\DbOptionsInterface;
use Kununu\TestingBundle\Test\Options\OptionsInterface;
use RuntimeException;

/***********************************************************************************************************************
 * @formatter:off
 ***********************************************************************************************************************
 * @method $this clearCachePoolFixtures(string $cachePoolServiceId)
 * @method $this clearElasticsearchFixtures(string $alias)
 * @method $this clearOpenSearchFixtures(string $alias)
 * @method $this clearHttpClientFixtures(string $httpClientServiceId)
 ***********************************************************************************************************************
 * @method array getCachePoolFixtures(string $cachePoolServiceId)
 * @method array getElasticsearchFixtures(string $alias)
 * @method array getOpenSearchFixtures(string $alias)
 * @method array getHttpClientFixtures(string $alias)
 ***********************************************************************************************************************
 * @method void loadCachePoolFixtures(string $cachePoolServiceId, OptionsInterface $options, string ...$classNames)
 * @method void loadElasticsearchFixtures(string $alias, OptionsInterface $options, string ...$classNames)
 * @method void loadOpenSearchFixtures(string $alias, OptionsInterface $options, string ...$classNames)
 * @method void loadHttpClientFixtures(string $httpClientServiceId, OptionsInterface $options, string ...$classNames)
 ***********************************************************************************************************************
 * @method void registerInitializableFixtureForDb(string $connectionName, string $className, mixed ...$args)
 * @method void registerInitializableFixtureForNonTransactionalDb(string $connectionName, string $className, mixed ...$args)
 * @method void registerInitializableFixtureForCachePool(string $cachePoolServiceId, string $className, mixed ...$args)
 * @method void registerInitializableFixtureForElasticsearch(string $alias, string $className, mixed ...$args)
 * @method void registerInitializableFixtureForOpenSearch(string $alias, string $className, mixed ...$args)
 * @method void registerInitializableFixtureForHttpClient(string $httpClientServiceId, string $className, mixed ...$args)
 ***********************************************************************************************************************
 * @formatter:on
 **********************************************************************************************************************/
abstract class FixturesAwareTestCase extends AbstractTestCase
{
    private const string KEY_CONNECTIONS = 'connections';
    private const string KEY_NON_TRANSACTIONAL_CONNECTIONS = 'non_transactional_connections';
    private const string KEY_CACHE_POOLS = 'cache_pools';
    private const string KEY_ELASTICSEARCH = 'elastic_search';
    private const string KEY_OPEN_SEARCH = 'open_search';
    private const string KEY_HTTP_CLIENT = 'http_client';

    private const array CLEAR_FIXTURES_METHODS = [
        'clearCachePoolFixtures'     => self::KEY_CACHE_POOLS,
        'clearElasticsearchFixtures' => self::KEY_ELASTICSEARCH,
        'clearOpenSearchFixtures'    => self::KEY_OPEN_SEARCH,
        'clearHttpClientFixtures'    => self::KEY_HTTP_CLIENT,
    ];

    private const array GET_FIXTURES_METHODS = [
        'getCachePoolFixtures'     => self::KEY_CACHE_POOLS,
        'getElasticsearchFixtures' => self::KEY_ELASTICSEARCH,
        'getOpenSearchFixtures'    => self::KEY_OPEN_SEARCH,
        'getHttpClientFixtures'    => self::KEY_HTTP_CLIENT,
    ];

    private const array LOAD_FIXTURES_METHODS = [
        'loadCachePoolFixtures'     => self::KEY_CACHE_POOLS,
        'loadElasticsearchFixtures' => self::KEY_ELASTICSEARCH,
        'loadOpenSearchFixtures'    => self::KEY_OPEN_SEARCH,
        'loadHttpClientFixtures'    => self::KEY_HTTP_CLIENT,
    ];

    private const array REGISTER_INITIALIZABLE_FIXTURE_METHODS = [
        'registerInitializableFixtureForDb'                 => self::KEY_CONNECTIONS,
        'registerInitializableFixtureForNonTransactionalDb' => self::KEY_NON_TRANSACTIONAL_CONNECTIONS,
        'registerInitializableFixtureForCachePool'          => self::KEY_CACHE_POOLS,
        'registerInitializableFixtureForElasticsearch'      => self::KEY_ELASTICSEARCH,
        'registerInitializableFixtureForOpenSearch'         => self::KEY_OPEN_SEARCH,
        'registerInitializableFixtureForHttpClient'         => self::KEY_HTTP_CLIENT,
    ];

    public function __call(string $method, array $args): array|self|static|null
    {
        if (array_key_exists($method, self::CLEAR_FIXTURES_METHODS)) {
            return $this->clearFixtures(self::CLEAR_FIXTURES_METHODS[$method], ...$args);
        }

        if (array_key_exists($method, self::GET_FIXTURES_METHODS)) {
            return $this->getFixtures(self::GET_FIXTURES_METHODS[$method], ...$args);
        }

        if (array_key_exists($method, self::LOAD_FIXTURES_METHODS)) {
            $this->loadFixtures(self::LOAD_FIXTURES_METHODS[$method], ...$args);

            return null;
        }

        if (array_key_exists($method, self::REGISTER_INITIALIZABLE_FIXTURE_METHODS)) {
            $this->registerInitializableFixture(self::REGISTER_INITIALIZABLE_FIXTURE_METHODS[$method], ...$args);

            return null;
        }

        throw new RuntimeException(sprintf('Unknown method "%s"!', $method));
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

    final protected function clearDbFixtures(string $connectionName, DbOptionsInterface $options): self
    {
        return $this->clearFixtures(
            $options->transactional() ? self::KEY_CONNECTIONS : self::KEY_NON_TRANSACTIONAL_CONNECTIONS,
            $connectionName
        );
    }

    final protected function getDbFixtures(string $connectionName, DbOptionsInterface $options): array
    {
        return $this->getFixtures(
            $options->transactional() ? self::KEY_CONNECTIONS : self::KEY_NON_TRANSACTIONAL_CONNECTIONS,
            $connectionName
        );
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
