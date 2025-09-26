# kununu testing-bundle

This bundle integrates with [kununu/data-fixtures](https://github.com/kununu/data-fixtures) package allowing you to load fixtures in your tests.

It also provides some utilities that makes testing easier, like a `RequestBuilder` that turns testing controllers more expressive. 

If you want to see an example of what this bundle can do for you click [here](docs/Test/web-test-case.md#example).

------------------------------------

## Install

#### 1. Add kununu/testing-bundle to your project

**Please be aware that this bundle should not be used in production mode!**

```shell
composer require --dev kununu/testing-bundle
```

#### 2. Enable Bundle

Enable the bundle at `config/bundles.php` for any environment.

```php
<?php
declare(strict_types=1);

return [
    ...
    Kununu\TestingBundle\KununuTestingBundle::class => ['dev' => true, 'test' => true],
];
```

--------------------

## Configuration

Create the file `kununu_testing.yaml` inside `config/packages/test/`.

The configuration options of the bundle heavily depend on the fixture type.

Check out the [Load Fixtures](#load-fixtures) section where you can find more options.

**Tip**
If you are using the bundle on more than one environment, for example *dev* and *test*, and the configuration options are exactly the same you can import the `kununu_testing.yaml` like bellow in order to not duplicate the configurations.

```yaml
# config/packages/dev/kununu_testing.yaml
kununu_testing:
  cache:
    pools:
      app.cache.first:
        load_command_fixtures_classes_namespace:
          - 'Kununu\TestingBundle\Tests\App\Fixtures\CachePool\CachePoolFixture1'
```

```yaml
# config/packages/test/kununu_testing.yaml
imports:
  - { resource: '../dev/kununu_testing.yaml' }
```

--------------------

## Load Fixtures

This bundle integrates with [kununu/data-fixtures](https://github.com/kununu/data-fixtures) allowing you to load fixtures in your tests.

Currently, this bundle supports the following types of fixtures:

- [Doctrine DBAL Connection Fixtures](docs/FixturesTypes/doctrine-dbal-connection-fixtures.md)
- [Cache Pool Fixtures](docs/FixturesTypes/cache-pool-fixtures.md)
- [DynamoDB Fixtures](docs/FixturesTypes/dynamodb.md)
- [Elasticsearch Fixtures](docs/FixturesTypes/elasticsearch.md)
- [OpenSearch Fixtures](docs/FixturesTypes/opensearch.md)
- [Symfony Http Client Fixtures](docs/FixturesTypes/symfony-http-client.md)

--------------------

## Schema Copier

-----------------------

This bundle also has a way of copying a database schema from one database to another.

See more:

- [Schema Copier](./docs/SchemaCopier/schema-copier.md)

------------------------------

## Testing your code

To test your code, load fixtures and call your endpoints, see:

- [FixtureAwareTextCase](docs/Test/fixtures-aware-test-case.md)
- [WebTestCase](docs/Test/web-test-case.md)
- [Request Builder](docs/Test/request-builder.md)

------------------------------

## Testing the bundle

This repository takes advantages of GitHub actions to run tests when a commit is performed to a branch.

If you want to run the integration tests on your local machine you will need:

- *pdo_mysql* extension
- MySQL server
- Elasticsearch cluster
- OpenSearch cluster

In your local environment to get everything ready for you, run `./tests/setupLocalTests.sh` and follow the instructions.

Then you can run the tests: `vendor/bin/phpunit`.

------------------------------

## Contribute

If you are interested in contributing read our [contributing guidelines](CONTRIBUTING.md).

------------------------------

![Continuous Integration](https://github.com/kununu/testing-bundle/actions/workflows/continuous-integration.yml/badge.svg)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=kununu_testing-bundle&metric=alert_status)](https://sonarcloud.io/dashboard?id=kununu_testing-bundle)
