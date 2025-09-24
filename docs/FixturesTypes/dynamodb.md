# DynamoDB Fixtures

This bundle integrates seamlessly with [kununu/data-fixtures](https://github.com/kununu/data-fixtures), allowing you to load fixtures into any configured DynamoDB table.

---

## Configuring DynamoDB Fixtures

First, configure the bundle by defining a DynamoDB table alias. In this example, we use an alias named `my_table_alias`:

```yaml
kununu_testing:
  dynamo_db:
    my_table_alias:
      service: 'Aws\DynamoDb\DynamoDbClient' # Your DynamoDB client service ID
      table_names: ['my_table_name', 'another_table'] # List of tables to manage
```

---

## Loading DynamoDB Fixtures in Tests

Extend either [`FixturesAwareTestCase`](../../src/Test/FixturesAwareTestCase.php) or [`WebTestCase`](../../src/Test/WebTestCase.php) in your tests to access the following method:

```php
protected function loadDynamoDbFixtures(string $alias, OptionsInterface $options, string ...$classNames): void
```

- `alias`: The alias defined in your configuration.
- `options`: [Options](options.md) for the fixture loading process.
- `classNames`: Fully qualified class names of the fixtures to load.

**Example: Loading fixtures in an integration test**

```php
use Kununu\TestingBundle\Test\FixturesAwareTestCase;
use Kununu\TestingBundle\Test\Options\Options;

final class IntegrationTest extends FixturesAwareTestCase
{
    public function testIntegration(): void
    {
        // Load Fixture1 into empty tables
        $this->loadDynamoDbFixtures(
            'my_table_alias',
            Options::create(),
            Fixture1::class
        );

        // Start from empty tables (no fixtures)
        $this->loadDynamoDbFixtures(
            'my_table_alias',
            Options::create()
        );

        // Load Fixture2 without purging tables first
        $this->loadDynamoDbFixtures(
            'my_table_alias',
            Options::create()->append(),
            Fixture2::class
        );
    }
}
```

## Loading Fixtures via Symfony Command

The bundle automatically registers Symfony console commands for each configured DynamoDB alias. This is useful for initializing tables with default data when your service starts.

**Usage:**

```shell
php bin/console kununu_testing:load_fixtures:dynamo_db:my_table_alias [--append]
```

If you omit the `--append` option, the tables will be purged before loading fixtures.

---

## Configuration

Below are all configuration options for DynamoDB fixtures:

```yaml
kununu_testing:
  dynamo_db:
    # Alias name (used in tests and commands)
    my_table_alias:
      # Required: DynamoDB client service ID
      service: 'Aws\DynamoDb\DynamoDbClient'

      # Required: List of table names to manage
      table_names:
        - 'user_profiles'
        - 'user_sessions'
        - 'tracking_data'

      # Optional: Fixture classes to load via command
      load_command_fixtures_classes_namespace:
        - 'App\Tests\Fixtures\DynamoDb\UserProfilesFixture'
        - 'App\Tests\Fixtures\DynamoDb\SessionsFixture'

    # You can configure multiple aliases
    another_alias:
      service: 'app.dynamodb.client.us_west'
      table_names: ['regional_data']
```

---

[Back to Index](../../README.md)
