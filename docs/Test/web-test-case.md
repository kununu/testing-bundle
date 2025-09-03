# WebTestCase

As an extension to the [FixturesAwareTestCase](fixtures-aware-test-case.md), this bundle offers the [WebTestCase](../../src/Test/WebTestCase.php). 

That abstract test case exposes a method that helps you to test your controllers without having to care about creating the kernel.

```php
final protected function doRequest(RequestBuilder $builder): Symfony\Component\HttpFoundation\Response
```

Internally this method calls the Symfony client with:

```php
$client->request($builder->method, $builder->uri, $builder->parameters, $builder->files, $builder->server, $builder->content);
```

And the parameters values are the ones returned by the [RequestBuilder](request-builder.md) `build` method.

--------------------------

## Example

Let's imagine that you have a route named *company_create*.

- That endpoint is protected (a valid access token needs to be provided)
- It a JSON to be provided in the body of the request with the data required to create a new company

```yaml
# routes.yaml
company_create:
  path:       /companies
  controller: App\Controller\CompaniesController::createAction
  methods:    [POST]
```

Using concepts provided by this bundle, like [Loading Fixtures](fixtures-aware-test-case.md), the [RequestBuilder](request-builder.md) and the [WebTestCase](#webtestcase) our test could like:

```php
<?php
declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Tests\Integration\Controller\DataFixtures\MySQL\CreateCompanyDataFixtures;
use Kununu\TestingBundle\Test\Options\DbOptions;
use Kununu\TestingBundle\Test\RequestBuilder;
use Kununu\TestingBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class CompaniesControllerTest extends WebTestCase
{
    public function testCreateCompany(): void
    {
        // Let's load a database fixture
        $this->loadDbFixtures('your_doctrine_connection_name', DbOptions::create(), CreateCompanyDataFixtures::class);

        $data = [
            'name'        => 'kununu GmbH',
            'location'    => [
                'city'         => 'Wien',
                'country_code' => 'at',
            ],
        ];

        // Let's use WebTestCase::doRequest to execute the request
        $response = $this->doRequest(
            // And build the request with the RequestBuilder
            RequestBuilder::aPostRequest()
                ->withUri('/companies')
                ->withContent($data)
                ->withAuthorization('eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjYyZDVkNzc5NmQxOTk')
                ->withServerParameter('REMOTE_ADDR', '127.0.0.1')
        );

        // Now we can do our assertions about the Response returned from the controller

        self::assertNotNull($response->getContent());
        self::assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $json = $response->getContent();
        self::assertJson($json);

        $company = json_decode($json, true);

        self::assertSame($data['name'], $company['name']);
        self::assertSame($data['location']['city'], $company['location']['city']);
        self::assertSame($data['location']['country_code'], $company['location']['country_code']);
    }
}
```

---

[Back to Index](../../README.md)
