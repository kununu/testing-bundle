# Elasticsearch Fixtures

This bundle integrates seamless with *Elasticsearch Fixtures* from [kununu/data-fixtures](https://github.com/kununu/data-fixtures).

----------------------------------

## How to load Elasticsearch Fixtures?

First you will need to configure the bundle. In this example, we will configure an Elasticsearch index named (aliased) *my_index_alias* that we will use in the rest of the documentation.

```
kununu_testing:
    elastic_search:
        my_index_alias:
            service: 'My\Elasticsearch\Client'
            index_name: 'my_index_name'
```

In your tests you can extend the classes [FixturesAwareTestCase](/src/Test/FixturesAwareTestCase.php) or [WebTestCase](/src/Test/WebTestCase.php) which expose the following method:

```php
loadElasticSearchFixtures(string $alias, array $classNames = [], bool $append = false, bool $clearFixtures = true)
```

- `$alias` - Alias defined above
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
        // Start with an empty index and loading data from Fixture1
        $this->loadElasticSearchFixtures(
            'my_index_alias',
            [Fixture1::class]
        );
        
        // Start from a empty index
        $this->loadElasticSearchFixtures(
            'my_index_alias',
            []
        );
        
        // Do not purge index before loading fixtures
        $this->loadElasticSearchFixtures(
            'my_index_alias',
            [Fixture1::class],
            true
        );
    }
}
```

-----------------------

## Symfony Command to load Elasticsearch fixtures

This bundle can automatically create a Symfony Command to load default fixtures for any configured Elasticsearch Index. This can be useful for example when you want to have default fixtures for a Elasticsearch Index that are loaded when your service spins up. At kununu we make use of this and when one of our services starts, we call a script, *run_startup.sh*, that on the *dev* and *test* environments calls this commands so that each Elasticsearch Index starts with a set of a default fixtures.

```bash
php bin/console kununu_testing:load_fixtures:elastic_search:MY_INDEX_ALIAS [--append]
```

### 1. Enable Symfony Command for a Elasticsearch Index

By default Symfony Commands are not created for any Elasticsearch Index. If you want to enable the creation of a Symfony Command for a specific Index you will need to enable it the configuration of the bundle by setting the option `load_command_fixtures_classes_namespace` where you specify the classes names of the fixtures that the command should run.

```yaml
kununu_testing:
    elastic_search:
        my_index_alias:
            load_command_fixtures_classes_namespace:
                - 'Kununu\TestingBundle\Tests\App\Fixtures\ElasticSearch\ElasticSearchFixture1'
                - 'Kununu\TestingBundle\Tests\App\Fixtures\ElasticSearch\ElasticSearchFixture2'
```

### 2. Run Symfony Command

The fixtures can be loaded for an Elasticsearch Index by running:

```bash
php bin/console kununu_testing:load_fixtures:elastic_search:my_index_alias --append
```

If `--append` option is not used then the cache pool will be purged.

------------------------------

## Initializable Fixtures

Since this bundle is using the [kununu/data-fixtures](https://github.com/kununu/data-fixtures) package, it also has support for initializable features, allowing you to inject arguments into your feature classes (see [documentation](https://github.com/kununu/data-fixtures) of the kununu/data-fixtures package).

In order to do that, your Fixtures classes must implement the *[InitializableFixtureInterface](https://github.com/kununu/data-fixtures/blob/master/src/InitializableFixtureInterface.php)*, and before loading the fixtures you will need to initialize the arguments.

```php
$this->registerInitializableFixtureForElasticSearch(
	'my_index_alias',
	YourElasticsearchFixtureClass::class,
	$yourArg1,
	...,
	$yourArgN
);

$this->loadElasticSearchFixtures(
	'my_index_alias',
	[
		YourElasticsearchFixtureClass::class
	]
);
```

-------------------------

## Configuration

Bellow you can find all configuration options for Elasticsearch fixtures.

```yaml
kununu_testing:
    elastic_search:
        my_index_alias: # Alias to be used to load fixtures for the configured index using the defined service
            load_command_fixtures_classes_namespace:
                - 'Kununu\TestingBundle\Tests\App\Fixtures\ElasticSearch\ElasticSearchFixture2' # FQDN for a fixtures class
            service: 'Kununu\TestingBundle\Tests\App\ElasticSearch' # Service Id of an instance of Elasticsearch\Client 
            index_name: 'my_index_name' # name of your index
```
