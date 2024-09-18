# Doctrine DBAL Connection Fixtures

This bundle integrates seamless with *Doctrine DBAL Connection Fixtures* from [kununu/data-fixtures](https://github.com/kununu/data-fixtures) and all configured Doctrine Connections are eligible to be used to load fixtures.

In the rest of the documentation we will assume that you are using the [Doctrine Bundle](https://github.com/doctrine/DoctrineBundle) and have a connection named *default*.

```yaml
doctrine:
  dbal:
    connections:
      default:
        driver: pdo_mysql
        url: '%env(resolve:DATABASE_URL)%'
```

----------------------------------

## How to load Doctrine Connection Fixtures?

In your tests you can extend the classes [FixturesAwareTestCase](../../src/Test/FixturesAwareTestCase.php) or [WebTestCase](../../src/Test/WebTestCase.php) which expose the following method:

```php
protected function loadDbFixtures(string $connectionName, DbOptionsInterface $options, string ...$classNames)
```

- `$connectionName` - Name of your connection
- `$options` - [Options](options.md) for the fixtures load process
- `$classNames` - Classes names of fixtures to load

**Example of loading fixtures in an Integration Test**

```php
use Kununu\TestingBundle\Test\FixturesAwareTestCase;
use Kununu\TestingBundle\Test\Options\DbOptions;

final class IntegrationTest extends FixturesAwareTestCase
{
    public function testIntegration(): void
    {
        // Start with an empty database and loading data from Fixture1
        $this->loadDbFixtures(
            'default',
            DbOptions::create(),
            Fixture1::class
        );
        
        // Start from a empty database
        $this->loadDbFixtures(
            'default',
            DbOptions::create()
        );
        
        // Do not purge Database before loading fixtures
        $this->loadDbFixtures(
            'default',
            DbOptions::create()->withAppend(),
            Fixture1::class
        );
    }
}
```
With non-transactional fixtures:

```php
use Kununu\TestingBundle\Test\FixturesAwareTestCase;
use Kununu\TestingBundle\Test\Options\DbOptions;

final class IntegrationTest extends FixturesAwareTestCase
{
    public function testIntegration(): void
    {
        // Start with an empty database and loading data from Fixture1
        $this->loadDbFixtures(
            'default',
            DbOptions::createNonTransactional(),
            Fixture1::class
        );
        
        // Start from a empty database
        $this->loadDbFixtures(
            'default',
            DbOptions::createNonTransactional()
        );
        
        // Do not purge Database before loading fixtures
        $this->loadDbFixtures(
            'default',
            DbOptions::createNonTransactional()->withAppend(),
            Fixture1::class
        );
    }
}
```

-----------------------

## Symfony Command to load Connection fixtures

This bundle can automatically create a Symfony Command to load default fixtures for any connection. This can be useful for example when you want to have default fixtures for a database that are loaded when your service spins up. At kununu we make use of this and when one of our services starts, we call a script, *run_startup.sh*, that on the *dev* and *test* environments calls this commands so that each database starts with a set of a default fixtures.

```shell
php bin/console kununu_testing:load_fixtures:connections:CONNECTION_NAME [--append]
```

Or for non-transactional fixtures:

```shell
php bin/console kununu_testing:load_fixtures:non_transactional_connections:CONNECTION_NAME [--append]
```

### 1. Enable Symfony Command for a Doctrine Connection

By default, Symfony Commands are not created for any Doctrine Connection. If you want to enable the creation of a Symfony Command for a specific Connection you will need to enable it the configuration of the bundle by setting the option `load_command_fixtures_classes_namespace` where you specify the classes names of the fixtures that the command should run.

```yaml
kununu_testing:
  connections:
    default:
      load_command_fixtures_classes_namespace:
        - 'Kununu\TestingBundle\Tests\App\Fixtures\Connection\ConnectionFixture1'
        - 'Kununu\TestingBundle\Tests\App\Fixtures\Connection\ConnectionFixture2'
  non_transactional_connections:
    default:
      load_command_fixtures_classes_namespace:
        - 'Kununu\TestingBundle\Tests\App\Fixtures\Connection\ConnectionFixture1'
        - 'Kununu\TestingBundle\Tests\App\Fixtures\Connection\ConnectionFixture2'
```

### 2. Run Symfony Command

The fixtures can be loaded for a Connection by running:

```shell
php bin/console kununu_testing:load_fixtures:connections:default --append
```

Or for non-transactional fixtures:

```shell
php bin/console kununu_testing:load_fixtures:non_transactional_connections:default --append
```

If `--append` option is not used then the connection will be purged.

------------------------------

## Initializable Fixtures

Since this bundle is using the [kununu/data-fixtures](https://github.com/kununu/data-fixtures) package, it also has support for initializable features, allowing you to inject arguments into your feature classes (see [documentation](https://github.com/kununu/data-fixtures) of the kununu/data-fixtures package).

In order to do that, your Fixtures classes must implement the *[InitializableFixtureInterface](https://github.com/kununu/data-fixtures/blob/master/src/InitializableFixtureInterface.php)*, and before loading the fixtures you will need to initialize the arguments.

```php
use Kununu\TestingBundle\Test\FixturesAwareTestCase;
use Kununu\TestingBundle\Test\Options\DbOptions;

final class IntegrationTest extends FixturesAwareTestCase
{
    public function testIntegration(): void
    {
        $this->registerInitializableFixtureForDb(
            'default',
            YourConnectionFixtureClass::class,
            $yourArg1,
            //...,
            $yourArgN
        );
        
        $this->loadDbFixtures(
            'default',
            DbOptions::create(),
            YourConnectionFixtureClass::class
        );
    }
}
```

Or for non-transactional fixtures:

```php
use Kununu\TestingBundle\Test\FixturesAwareTestCase;
use Kununu\TestingBundle\Test\Options\DbOptions;

final class IntegrationTest extends FixturesAwareTestCase
{
    public function testIntegration(): void
    {
        $this->registerInitializableFixtureForNonTransactionalDb(
	        'default',
	        YourConnectionFixtureClass::class,
	        $yourArg1,
	        //...,
            $yourArgN
        );

        $this->loadDbFixtures(
	        'default',
	        DbOptions::createNonTransactional(),
		    YourConnectionFixtureClass::class
        );
    }
}
```

-------------------------

## Configuration

Bellow you can find all configuration options for Doctrine Connection fixtures.

```yaml
kununu_testing:
  connections:
    connection_name:
      load_command_fixtures_classes_namespace: # FQDN for fixtures classes that the Symfony command will use
        - 'Kununu\TestingBundle\Tests\App\Fixtures\Connection\ConnectionFixture3'
      excluded_tables: # List of tables to exclude from being purged
        - table_to_exclude_from_purge
  non_transactional_connections:
    connection_name:
      load_command_fixtures_classes_namespace: # FQDN for fixtures classes that the Symfony command will use
        - 'Kununu\TestingBundle\Tests\App\Fixtures\Connection\ConnectionFixture3'
      excluded_tables: # List of tables to exclude from being purged
        - table_to_exclude_from_purge
```
