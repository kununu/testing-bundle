# kununu testing-bundle

This bundle integrates with [kununu/data-fixtures](https://github.com/kununu/data-fixtures) package allowing you to load fixtures in your tests. 
It also provides some utilities that makes testing easier, like a RequestBuilder that turns testing controllers more expressive. If you want to see an example of what this bundle can do for you click [here](#Example).

------------------------------------

## Install

#### 1. Add kununu/testing-bundle to your project

**Please be aware that this bundle should not be used in production mode!**

```bash
composer require --dev kununu/testing-bundle
```

#### 2. Enable Bundle

Enable the bundle at `config/bundles.php` for any environment.

```
<?php

return [
    ...
    Kununu\TestingBundle\KununuTestingBundle::class => ['dev' => true, 'test' => true],
];
```

--------------------

## Configuration

Create the file `kununu_testing.yaml` inside `config/packages/test/`.
The configuration options of the bundle heavily depend on the fixture type. Check out the [Load Fixtures](#Load-Fixtures) section where you can find more options.

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

- [Doctrine DBAL Connection Fixtures](/docs/FixturesTypes/doctrine-dbal-connection-fixtures.md)
- [Cache Pool Fixtures](/docs/FixturesTypes/cache-pool-fixtures.md)
- [Elasticsearch Fixtures](/docs/FixturesTypes/elasticsearch.md)

------------------------------

## Making a Request

#### Request Builder

This bundle provides a [Request Builder](https://github.com/kununu/testing-bundle/blob/master/src/Test/RequestBuilder.php) which makes calling an endpoint more expressive.

```php
// Creates and returns a Builder that you can use to do a GET request
public static function aGetRequest(): self;

// Creates and returns a Builder that you can use to do a POST request
public static function aPostRequest(): self;

// Creates and returns a Builder that you can use to do a DELETE request
public static function aDeleteRequest(): self;

// Creates and returns a Builder that you can use to do a PUT request
public static function aPutRequest(): self;

// Set The Request parameters
public function withParameters(array $parameters): self;

// Change The request method
public function withMethod(string $method): self;

// Set the URI to fetch
public function withUri(string $uri): self;

// Set the content of the request as an array that internally is transformed to a json and provided as the raw body data
public function withContent(array $content): self;

// Set the Raw body data
public function withRawContent(string $content): self;

// Sets an HTTP_AUTHORIZATION header with the value of "Bearer $token"
public function withAuthorization(string $token): self;

// Sets an header. 
// In converts any header name to uppercase and prepends "HTTP_" if the header name does not contains it
public function withHeader(string $headerName, string $headerValue): self;

// Sets a server parameter (HTTP headers are referenced with an HTTP_ prefix as PHP does)
public function withServerParameter(string $parameterName, string $parameterValue): self;
```

#### WebTestCase

This bundle exposes the [WebTestCase](https://github.com/kununu/testing-bundle/blob/master/src/Test/WebTestCase.php) that you can extend which exposes a method that helps you testing your controllers without having to care about create the kernel. This class also allows you load fixtures in your tests.

```php
protected function doRequest(RequestBuilder $builder): Symfony\Component\HttpFoundation\Response
```

Internally this method calls the Symfony client with:

```php
$client->request($builder->method, $builder->uri, $builder->parameters, $builder->files, $builder->server, $builder->content);
```

--------------------------

## Example

Lets imagine that you have a route named *company_create* which is protected (A valid access token needs to be provided) and expects a json to be provided in the body of the request with the data required to create a new company.

```yaml
# routes.yaml
company_create:
  path:       /companies
  controller: App\Controller\CompaniesController::createAction
  methods:    [POST]
```

Using concepts provided by this bundle, like *Loading Fixtures*, the *RequestBuilder* and the *WebTestCase* our test could like:

```php
<?php

namespace App\Tests\Functional\Controller;

use App\Tests\Functional\Controller\DataFixtures\MySQL\CreateCompanyDataFixtures;
use Kununu\TestingBundle\Test\RequestBuilder;
use Kununu\TestingBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CompaniesControllerTest extends WebTestCase
{
    public function testCreateCompany(): void
    {
        $this->loadDbFixtures('your_doctrine_connection_name', [CreateCompanyDataFixtures::class]);

        $data = [
            'name'        => 'kununu GmbH',
            'location'    => [
                'city'         => 'Wien',
                'country_code' => 'at',
            ],
        ];

        $response = $this->doRequest(
            RequestBuilder::aPostRequest()
                ->withUri('/companies')
                ->withContent($data)
                ->withAuthorization('eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjYyZDVkNzc5NmQxOTk')
                ->withServerParameter('REMOTE_ADDR', '127.0.0.1')
        );

        $this->assertNotNull($response->getContent());
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $json = $response->getContent();
        $this->assertJson($json);

        $company = json_decode($json, true);

        $this->assertSame($data['name'], $company['name']);
        $this->assertSame($data['location']['city'], $company['location']['city']);
        $this->assertSame($data['location']['country_code'], $company['location']['country_code']);
    }
}
```

------------------------------

## Contribute

If you are interested in contributing read our [contributing guidelines](/CONTRIBUTING.md).

------------------------------


## Tests

This repository takes advantages of GitHub actions to run tests when a commit is performed to a branch.

If you want to run the integration tests on your local machine you will need:
- *pdo_mysql* extension
- MySQL server
- Elasticsearch cluster

In your local environment to get everything ready for you, run `./tests/setupLocalTests.sh` and follow the instructions.
Then you can run the tests: `vendor/bin/phpunit`.

------------------------------

![Continuous Integration](https://github.com/kununu/testing-bundle/actions/workflows/continuous-integration.yml/badge.svg)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=kununu_testing-bundle&metric=alert_status)](https://sonarcloud.io/dashboard?id=kununu_testing-bundle)
