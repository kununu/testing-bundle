# OpenSearch Fixtures

This bundle integrates seamless with *OpenSearch Fixtures* from [kununu/data-fixtures](https://github.com/kununu/data-fixtures).

----------------------------------

## How to load OpenSearch Fixtures?

First you will need to configure the bundle. In this example, we will configure an OpenSearch index named (aliased) *my_index_alias* that we will use in the rest of the documentation.

```yaml
kununu_testing:
  open_search:
    my_index_alias:
      service: 'OpenSearch\Client' # Your OpenSearch client service id
      index_name: 'my_index_name'
```

In your tests you can extend the classes [FixturesAwareTestCase](../../src/Test/FixturesAwareTestCase.php) or [WebTestCase](../../src/Test/WebTestCase.php) which expose the following method:

```php
protected function loadOpenSearchFixtures(string $alias, OptionsInterface $options, string ...$classNames): void
```

- `$alias` - Alias defined above
- `$classNames` - Array with classes names of fixtures to load
- `$options` - [Options](options.md) for the fixtures load process
- `$classNames` - Classes names of fixtures to load

**Example of loading fixtures in an Integration Test**

```php
use Kununu\TestingBundle\Test\FixturesAwareTestCase;
use Kununu\TestingBundle\Test\Options\Options;

final class IntegrationTest extends FixturesAwareTestCase
{
    public function testIntegration()
    {
        // Start with an empty index and loading data from Fixture1
        $this->loadOpenSearchFixtures(
            'my_index_alias',
            Options::create(),
            Fixture1::class
        );
        
        // Start from a empty index
        $this->loadOpenSearchFixtures(
            'my_index_alias',
            Options::create()
        );
        
        // Do not purge index before loading fixtures
        $this->loadOpenSearchFixtures(
            'my_index_alias',
            Options::create()->withAppend(),
            Fixture1::class
        );
    }
}
```

-----------------------

## Symfony Command to load OpenSearch fixtures

This bundle can automatically create a Symfony Command to load default fixtures for any configured OpenSearch Index. 

This can be useful for example when you want to have default fixtures for a OpenSearch Index that are loaded when your service spins up.

At kununu we make use of this and when one of our services starts, we call a script, *run_startup.sh*, that on the *dev* and *test* environments calls this commands so that each OpenSearch Index starts with a set of a default fixtures.

```shell
php bin/console kununu_testing:load_fixtures:open_search:MY_INDEX_ALIAS [--append]
```

### 1. Enable Symfony Command for an OpenSearch Index

By default, Symfony Commands are not created for any OpenSearch Index. 

If you want to enable the creation of a Symfony Command for a specific Index you will need to enable it in the configuration of the bundle by setting the option `load_command_fixtures_classes_namespace` where you specify the classes names of the fixtures that the command should run.

```yaml
kununu_testing:
  open_search:
    my_index_alias:
      load_command_fixtures_classes_namespace:
        - 'Kununu\TestingBundle\Tests\App\Fixtures\OpenSearch\OpenSearchFixture1'
        - 'Kununu\TestingBundle\Tests\App\Fixtures\OpenSearch\OpenSearchFixture2'
```

### 2. Run Symfony Command

The fixtures can be loaded for an OpenSearch Index by running:

```shell
php bin/console kununu_testing:load_fixtures:open_search:my_index_alias --append
```

If `--append` option is not used then the index will be purged.

------------------------------

## Initializable Fixtures

Since this bundle is using the [kununu/data-fixtures](https://github.com/kununu/data-fixtures) package, it also has support for initializable features, allowing you to inject arguments into your feature classes (see [documentation](https://github.com/kununu/data-fixtures) of the kununu/data-fixtures package).

In order to do that, your Fixtures classes must implement the *[InitializableFixtureInterface](https://github.com/kununu/data-fixtures/blob/master/src/InitializableFixtureInterface.php)*, and before loading the fixtures you will need to initialize the arguments.

```php
use Kununu\TestingBundle\Test\FixturesAwareTestCase;
use Kununu\TestingBundle\Test\Options\Options;

final class IntegrationTest extends FixturesAwareTestCase
{
    public function testIntegration()
    {
        $this->registerInitializableFixtureForOpenSearch(
            'my_index_alias',
            YourOpenSearchFixtureClass::class,
            $yourArg1,
            // ...,
            $yourArgN
        );

        $this->loadOpenSearchFixtures(
            'my_index_alias',
            Options::create(),
            YourOpenSearchFixtureClass::class
        );
    }
}
```

-------------------------

## Configuration

Bellow you can find all configuration options for OpenSearch fixtures.

```yaml
kununu_testing:
  open_search:
    my_index_alias: # Alias to be used to load fixtures for the configured index using the defined service
      load_command_fixtures_classes_namespace:
        - 'Kununu\TestingBundle\Tests\App\Fixtures\OpenSearch\OpenSearchFixture2' # FQDN for a fixtures class
      service: 'Kununu\TestingBundle\Tests\App\OpenSearch' # Service Id of an instance of OpenSearch\Client 
      index_name: 'my_index_name' # name of your index
```

---

[Back to Index](../../README.md)
