# kununu testing-bundle

At kununu we do functional and integration tests. [LiipFunctionalTestBundle](https://github.com/liip/LiipFunctionalTestBundle) and [DoctrineTestBundle](https://github.com/dmaicher/doctrine-test-bundle) are great options however they do not match our requirements and heavily depend on [Doctrine ORM](https://github.com/doctrine/orm).

Also we have the necessity to load database fixtures for DEV/TEST/E2E environments.

The main requirements that we had to solve that this bundle addresses are:
- **Database schema is not touched when loading fixtures**. This requirement excludes LiipFunctionalTestBundle because it drops and creates the schema when loading fixtures. Another drawback of LiipFunctionalTestBundle is that it relies on Doctrine Mapping Metadata to recreate the schema which for us is a limitation since we do not always map everything but instead use Migrations.
- **We really want to hit the database**. This requirement excludes DoctrineTestBundle because it wraps your fixtures in a transaction.

Apart from solving the requirements above, this bundle eases the use of [KununuDataFixtures](https://github.com/kununu/data-fixtures) package.
It also provides some utilities to use use in your tests.

## Loading Fixtures in your tests

### CachePool Fixtures

All services tagged with `cache.pool` are elegible to be used to load fixtures.

For example, assuming you are using the [Cache Component](https://symfony.com/doc/current/components/cache.html):

```
framework:
    cache:
        pools:
            app.cache.first:
                adapter: cache.adapter.memcached
```

In your tests you can extend the classes `FixturesAwareTestCase` or `WebTestCase` which expose the following method:

```
loadCachePoolFixtures(string $cachePoolServiceId, array $classNames = [], bool $append = false)
```

- `$cachePoolServiceId` - Name of your pool as configured in the config above
- `$classNames` - Array with classes names of fixtures to load
- `$append` - If `false` the cache pool will be purged before loading your fixtures

**Example**

```
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

### Connection Fixtures

All Doctrine Connections are eligible to be used to load fixtures.
For example, assuming you are using the [Doctrine Bundle](https://github.com/doctrine/DoctrineBundle).

```
doctrine:
    dbal:
        connections:
            default:
                driver: pdo_mysql
                url: '%env(resolve:DATABASE_URL)%'
```

In your tests you can extend the classes `FixturesAwareTestCase` or `WebTestCase` which expose the following method:

```
loadDbFixtures(string $connectionName, array $classNames = [], bool $append = false)
```

- `$connectionName` - Name of your connection
- `$classNames` - Array with classes names of fixtures to load
- `$append` - If `false` the cache pool will be purged before loading your fixtures

**Example**

```
use Kununu\TestingBundle\Test\FixturesAwareTestCase;

final class IntegrationTest extends FixturesAwareTestCase
{
    public function testIntegration()
    {
        // Start with an empty database and loading data from Fixture1
        $this->loadDbFixtures(
            'default',
            [Fixture1::class]
        );
        
        // Start from a empty database
        $this->loadDbFixtures(
            'default',
            []
        );
        
        // Do not purge Database before loading fixtures
        $this->loadDbFixtures(
            'default',
            [
                Fixture1::class
            ],
            true
        );
    }
}
```

#### Command to load database fixtures

This bundles can automatically create a command to load database fixtures.

```
php bin/console kununu_testing:load_fixtures:connections:CONNECTION_NAME [--append]
```

There is the need to define the files with the fixtures in the configuration of the bundle

```
# kununu_testing.yaml

kununu_testing:
    connections:
        default:
            load_command_fixtures_classes_namespace:
                - 'Kununu\TestingBundle\Tests\App\Fixtures\Connection\ConnectionFixture3' # FQDN for a fixtures class
```

Then the fixtures can be loaded running:

```
php bin/console kununu_testing:load_fixtures:connections:default --append
```

If `--append` option is not used, then the database will be truncated. A prompt appears to confirm database truncation.

### Elasticsearch Fixtures

If you want to load Elasticsearch fixtures in your tests first you will need to configure the bundle:

```
kununu_testing:
    elastic_search:
        my_index_alias:
            service: 'My\Elasticsearch\Client'
            index_name: 'my_index_name'
```

In your tests you can extend the classes `FixturesAwareTestCase` or `WebTestCase` which expose the following method:

```
loadElasticSearchFixtures(string $alias, array $classNames = [], bool $append = false)
```

- `$alias` - Alias defined above
- `$classNames` - Array with classes names of fixtures to load
- `$append` - If `false` the cache pool will be purged before loading your fixtures

**Example**

```
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

## Making a Request

The class `WebTestCase` exposes two methods that help you testing your controllers:

```
getClient() : Client
doRequest(Client $client, RequestBuilder $builder): Response
```

It also provides you a Request Builder. Please check `Kununu\TestingBundle\Test\RequestBuilder`.

**Example**

```
use Kununu\TestingBundle\Test\RequestBuilder;
use Kununu\TestingBundle\Test\WebTestCase;

final class WebTestCaseTest extends WebTestCase
{
    public function testDoRequest()
    {
        $response = $this->doRequest(
            $this->getClient(),
            RequestBuilder::aGetRequest()->withUri('/app/response')
        );

        $this->assertEquals('{"key":"value"}', $response->getContent());
    }
}
```


## Configuration

```
# kununu_testing.yaml

kununu_testing:
    connections:
        connection_name:
            load_command_fixtures_classes_namespace:
                - 'Kununu\TestingBundle\Tests\App\Fixtures\Connection\ConnectionFixture3' # FQDN for a fixtures class
            excluded_tables:
                - table_to_exclude_from_purge
                
    elastic_search:
        my_index_alias: # Alias to be use to load fixtures for the configured index using the defined service
            service: 'Kununu\TestingBundle\Tests\App\ElasticSearch' # Service Id of an instance of Elasticsearch\Client 
            index_name: 'my_index_name' # name of your index
```

## Tests

Run the tests by doing:

```
composer install

kununu test lib testing-bundle [--exclude-group integration]
# OR
vendor/phpunit/phpunit/phpunit tests [--exclude-group integration]
```

**If you want to run the integration tests you will need the extension `ext-pdo_sqlite` (For installing int ubuntu run `apt update && apt install php-sqlite3`).**
**If you want to run the integration tests you will need to have an Elasticsearch cluster running. To change the hosts of the cluster please go to `tests/App/config/packages/parameters.yml`.**
