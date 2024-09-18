# Symfony Http Client Fixtures

This bundle integrates seamless with *Symfony Http Client Fixtures* from [kununu/data-fixtures](https://github.com/kununu/data-fixtures).

In the rest of the documentation we will assume that you are using the [Symfony Http Client](https://github.com/symfony/http-client) and [Symfony Http Foundation](https://github.com/symfony/http-foundation) and have configured an Http client service called *app.my.http_client*.

----------------------------------

## How to load Symfony Http Client Fixtures?

In your tests you can extend the classes [FixturesAwareTestCase](../../src/Test/FixturesAwareTestCase.php) or [WebTestCase](../../src/Test/WebTestCase.php) which expose the following method:

```php
protected function loadHttpClientFixtures(string $httpClientServiceId, OptionsInterface $options, string ...$classNames): void
```

- `$httpClientServiceId` - Name of your Symfony Http Client service
- `$options` - [Options](options.md) for the fixtures load process
- `$classNames` - Classes names of fixtures to load

**Example of loading fixtures in an Integration Test**

```php
use Kununu\TestingBundle\Test\FixturesAwareTestCase;
use Kununu\TestingBundle\Test\Options\Options;

final class IntegrationTest extends FixturesAwareTestCase
{
    public function testIntegration(): void
    {
        // Load mock responses of a Symfony Http Client from a Fixture class
        $this->loadHttpClientFixtures(
            'app.my.http_client',
            Options::create(),
            YourHttpClientFixtureClass::class
        );
    }
}
```

## Initializable Fixtures

Since this bundle is using the [kununu/data-fixtures](https://github.com/kununu/data-fixtures) package, it also has support for initializable features, allowing you to inject arguments into your feature classes (see [documentation](https://github.com/kununu/data-fixtures) of the kununu/data-fixtures package).

In order to do that, your Fixtures classes must implement the *[InitializableFixtureInterface](https://github.com/kununu/data-fixtures/blob/master/src/InitializableFixtureInterface.php)*, and before loading the fixtures you will need to initialize the arguments.

```php
use Kununu\TestingBundle\Test\FixturesAwareTestCase;
use Kununu\TestingBundle\Test\Options\Options;

final class IntegrationTest extends FixturesAwareTestCase
{
    public function testIntegration(): void
    {
        $this->registerInitializableFixtureForHttpClient(
            'app.my.http_client',
	        YourHttpClientFixtureClass::class,
	        $yourArg1,
	        //...,
	        $yourArgN
        );

        $this->loadHttpClientFixtures(
	        'app.my.http_client',
	        Options::create(),
	        YourHttpClientFixtureClass::class
        );
    }
}

```

-------------------------

## Configuration

Bellow you can find all configuration options for symfony http client fixtures and their defaults.

```yaml
kununu_testing:
  http_client:
    clients:
      - 'app.my.http_client'
```

Also make sure that in your *test* environment configuration you redefine your Http client instances to use the special Http Mock client provided by [kununu/data-fixtures](https://github.com/kununu/data-fixtures), otherwise the fixtures won't be loaded:

```yaml
services:
  app.my.http_client:
    class: Kununu\DataFixtures\Tools\HttpClient
    public: true
```

Also be mindful that **if you inject the same client on several services** you might have **unwanted results**.

Example: you are testing service A which uses component B. Component B is also injected with the same Http client but is calling totally different endpoints.

To solve those cases create dedicated Http clients services for the service you are testing.

### Common Problems and Solutions

#### The mocked Http Client is not being used

- Try to create an alias of the Symfony interface to your client in your *test* environment configuration:

```yaml
services:
  http_client:
    class: Kununu\DataFixtures\Tools\HttpClient
    public: true

  Symfony\Contracts\HttpClient\HttpClientInterface: '@http_client'
```

- If the mock Http client is still not loaded make sure to add to your *test* environment `framework.yaml` file:

```yaml
framework:
  http_client:
    enabled: false
```

To disable Symfony Framework bundle creating its own Http clients.
