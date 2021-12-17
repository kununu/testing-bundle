# Cache Pool Fixtures

This bundle integrates seamless with *Cache Pool Fixtures* from [kununu/data-fixtures](https://github.com/kununu/data-fixtures) and by default all services tagged with `cache.pool` are eligible to be used to load fixtures.

In the rest of the documentation we will assume that you are using the [Symfony Cache Component](https://symfony.com/doc/current/components/cache.html) and have configured a cache pool named *app.cache.first*.

```yaml
framework:
  cache:
    pools:
      app.cache.first:
        adapter: cache.adapter.memcached
```

----------------------------------

## How to load Cache Pool Fixtures?

In your tests you can extend the classes [FixturesAwareTestCase](/src/Test/FixturesAwareTestCase.php) or [WebTestCase](/src/Test/WebTestCase.php) which expose the following method:

```php
protected function loadCachePoolFixtures(string $cachePoolServiceId, array $classNames = [], bool $append = false, bool $clearFixtures = true)
```

- `$cachePoolServiceId` - Name of your pool as configured in the config above
- `$classNames` - Array with classes names of fixtures to load
- `$append` - If `false` the cache pool will be purged before loading your fixtures
- `$clearFixtures` - If `true` it will clear any previous loaded fixtures classes


**Example of loading fixtures in a Integration Test**

```php
use Kununu\TestingBundle\Test\FixturesAwareTestCase;

final class IntegrationTest extends FixturesAwareTestCase
{
    public function testIntegration()
    {
        // Start with a clean pool loading data from Fixture1
        $this->loadCachePoolFixtures(
            'app.cache.first',
            [
                Fixture1::class
            ]
        );
        
        // Start from a clean pool
        $this->loadCachePoolFixtures(
            'app.cache.first',
            []
        );
        
        // Do not purge pool before loading fixtures
        $this->loadCachePoolFixtures(
            'app.cache.first',
            [
                Fixture1::class
            ],
            true
        );
    }
}
```

You can also disable the creation of orchestrators services for cache pools if you don't want to use fixtures on cache pools (see [configuration](#Configuration)).

-----------------------

## Symfony Command to load Cache fixtures

This bundle can automatically create a Symfony Command to load default fixtures for any cache pool. This can be useful for example when you want to have default fixtures for a cache pool that are loaded when your service spins up. At kununu we make use of this and when one of our services starts, we call a script, *run_startup.sh*, that on the *dev* and *test* environments calls this commands so that each cache pool starts with a set of a default fixtures.

```bash
php bin/console kununu_testing:load_fixtures:cache_pools:MY_CACHE_ID [--append]
```

### 1. Enable Symfony Command for a Cache Pool

By default Symfony Commands are not created for any cache pool. If you want to enable the creation of a Symfony Command for a specific cache pool you will need to enable it the configuration of the bundle by setting the option `load_command_fixtures_classes_namespace` where you specify the classes names of the fixtures that the command should run.

```yaml
kununu_testing:
  cache:
    pools:
      app.cache.first:
        load_command_fixtures_classes_namespace:
          - 'Kununu\TestingBundle\Tests\App\Fixtures\CachePool\CachePoolFixture1'
          - 'Kununu\TestingBundle\Tests\App\Fixtures\CachePool\CachePoolFixture2'
```

### 2. Run Symfony Command

The fixtures can be loaded for a cache pool by running:

```bash
php bin/console kununu_testing:load_fixtures:cache_pools:app.cache.first --append
```

If `--append` option is not used then the cache pool will be purged.

------------------------------

## Initializable Fixtures

Since this bundle is using the [kununu/data-fixtures](https://github.com/kununu/data-fixtures) package, it also has support for initializable features, allowing you to inject arguments into your feature classes (see [documentation](https://github.com/kununu/data-fixtures) of the kununu/data-fixtures package).

In order to do that, your Fixtures classes must implement the *[InitializableFixtureInterface](https://github.com/kununu/data-fixtures/blob/master/src/InitializableFixtureInterface.php)*, and before loading the fixtures you will need to initialize the arguments.

```php
$this->registerInitializableFixtureForCachePool(
	'app.cache.first',
	YourCachePoolFixtureClass::class,
	$yourArg1,
	...,
	$yourArgN
);

$this->loadCachePoolFixtures(
	'app.cache.first',
    [
    	YourCachePoolFixtureClass::class
    ]
);
```

-------------------------

## Configuration

Bellow you can find all configuration options for cache pool fixtures and their defaults.

```yaml
kununu_testing:
  cache:
    enable: true # Enable or disable the generation of orchestrators for cache pools in the app
    pools:
      app.cache.first: # Cache pool Id
        load_command_fixtures_classes_namespace: # FQDN for fixtures classes that the Symfony command will use
          - 'Kununu\TestingBundle\Tests\App\Fixtures\CachePool\CachePoolFixture1'
          - 'Kununu\TestingBundle\Tests\App\Fixtures\CachePool\CachePoolFixture2'
```
