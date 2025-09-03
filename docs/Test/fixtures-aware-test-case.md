# FixturesAwareTestCase

The main idea of using the bundle is to be able to configure and use data fixtures in your test methods. This test case provides methods that will allow you precisely that.

See [Load Fixtures](../../README.md#load-fixtures) for a list of fixtures types available and check each fixture documentation page to learn how to configure and use them and also get examples.

## Clear Fixtures methods

These are the methods available to clear loaded fixtures:

```php
final protected function clearCachePoolFixtures(string $cachePoolServiceId): static;

final protected function clearDbFixtures(string $connectionName, DbOptionsInterface $options): static;

final protected function clearElasticsearchFixtures(string $alias): static

final protected function clearHttpClientFixtures(string $httpClientServiceId): static;

final protected function clearOpenSearchFixtures(string $alias): static;
```

## Get Fixtures methods

These are the methods available to get already loaded fixtures:

```php
final protected function getCachePoolFixtures(string $cachePoolServiceId): array;

final protected function getDbFixtures(string $connectionName, DbOptionsInterface $options): array;

final protected function getElasticsearchFixtures(string $alias): array;

final protected function getHttpClientFixtures(string $httpClientServiceId): array;

final protected function getOpenSearchFixtures(string $alias): array;
```

## Load Fixtures methods

These are the methods available to load fixtures:

```php
final protected function loadCachePoolFixtures(string $cachePoolServiceId, OptionsInterface $options, string ...$classNames): void;

final protected function loadDbFixtures(string $connectionName, DbOptionsInterface $options, string ...$classNames): void;
    
final protected function loadElasticsearchFixtures(string $alias, OptionsInterface $options, string ...$classNames): void;

final protected function loadHttpClientFixtures(string $httpClientServiceId, OptionsInterface $options, string ...$className): void;

final protected function loadOpenSearchFixtures(string $alias, OptionsInterface $options, string ...$className): void;
```

## Register Initializable Fixtures methods

These are the methods available to register initializable fixtures:

```php
final protected function registerInitializableFixtureForCachePool(string $cachePoolServiceId, string $className, mixed ...$args): void

final protected function registerInitializableFixtureForDb(string $connectionName, string $className, mixed ...$args): void;

final protected function registerInitializableFixtureForElasticsearch(string $alias, string $className, mixed ...$args): void;

final protected function registerInitializableFixtureForHttpClient(string $httpClientServiceId, string $className, mixed ...$args): void;

final protected function registerInitializableFixtureForNonTransactionalDb(string $connectionName, string $className, mixed ...$args): void;

final protected function registerInitializableFixtureForOpenSearch(string $alias, string $className, mixed ...$args): void
```

---

[Back to Index](../../README.md)
