# kununu testing-bundle

### What is kununu/testing-bundle?

At kununu we usually write functional and integration tests. [liip/LiipFunctionalTestBundle](https://github.com/liip/LiipFunctionalTestBundle) and [dmaicher/doctrine-test-bundle](https://github.com/dmaicher/doctrine-test-bundle) are great options however they do not match our requirements and heavily depend on [Doctrine ORM](https://github.com/doctrine/orm).
Also, we have the necessity to load fixtures in our *dev/test/e2e* environments for any type of storage that our services use.

The main requirements that we address with this bundle:

- **Database schema is not touched when loading fixtures**. This requirement excludes LiipFunctionalTestBundle because it drops and creates the schema when loading fixtures. Another drawback of LiipFunctionalTestBundle is that it relies on Doctrine Mapping Metadata to recreate the schema which for us is a limitation since we do not always map everything but instead use Migrations.
- **We really want to hit the databases**. This requirement excludes https://github.com/dmaicher/doctrine-test-bundle because it wraps your fixtures in a transaction.

Apart from solving the requirements above this bundle easily integrates with [kununu/data-fixtures](https://github.com/kununu/data-fixtures) package and provides some utilities that makes testing easier, like a RequestBuilder that turns testing controllers more expressive. If you want to see an example of what this bundle can do for you click [here](#Example).

------------------------------------

## Install

#### 1. Add kununu/testing-bundle to your project

**Please beaware that this bundle should not be used in production mode!**

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

**If you want to run the integration tests on your local machine you will need the extension `pdo_mysql` installed, a running local MySql database and  an Elasticsearch cluster running.**

To setup your local environment please copy the file `test/.env` to `test/.env.test` and change the connections parameters to reflect your local env then run `tests/setupLocalTest.sh`

Now you can run your tests locally: `vendor/bin/phpunit`.
