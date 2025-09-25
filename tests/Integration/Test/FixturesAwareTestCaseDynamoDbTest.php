<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Integration\Test;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Kununu\TestingBundle\Test\FixturesAwareTestCase;
use Kununu\TestingBundle\Test\FixturesContainerGetterTrait;
use Kununu\TestingBundle\Test\Options\Options;
use Kununu\TestingBundle\Tests\App\Fixtures\DynamoDb\DynamoDbFixture1;
use Kununu\TestingBundle\Tests\App\Fixtures\DynamoDb\DynamoDbFixture2;

final class FixturesAwareTestCaseDynamoDbTest extends FixturesAwareTestCase
{
    use FixturesContainerGetterTrait;

    private DynamoDbClient $client;

    public function testLoadDynamoDbFixturesWithoutAppend(): void
    {
        // First, add an item that should be purged
        $this->client->putItem([
            'TableName' => 'my_table',
            'Item'      => [
                'attr_1' => ['S' => 'item_to_purge'],
            ],
        ]);

        $this->registerInitializableFixtureForDynamoDb(
            'my_table',
            DynamoDbFixture1::class,
            'my_table',
            ['test_param' => 'value']
        );

        $this->loadDynamoDbFixtures(
            'other_table',
            Options::create(),
            DynamoDbFixture1::class,
            DynamoDbFixture2::class
        );

        // Verify the item was purged
        $purgedItem = null;
        try {
            $purgedItem = $this->client->getItem([
                'TableName' => 'my_table',
                'Key'       => ['attr_1' => ['S' => 'item_to_purge']],
            ]);
        } catch (DynamoDbException) {
            // Item should not exist
        }

        if (!empty($purgedItem['Item'])) {
            $this->fail('Item should have been purged!');
        }

        // Verify fixtures were loaded
        $item1 = $this->client->getItem([
            'TableName' => 'my_table',
            'Key'       => ['attr_1' => ['S' => 'Test 1']],
        ]);
        $item2 = $this->client->getItem([
            'TableName' => 'my_table',
            'Key'       => ['attr_1' => ['S' => 'Test 3']],
        ]);
        $item3 = $this->client->getItem([
            'TableName' => 'my_table',
            'Key'       => ['attr_1' => ['S' => 'Test 5']],
        ]);

        self::assertEquals('Test 1', $item1['Item']['attr_1']['S']);
        self::assertEquals('Test 2', $item1['Item']['attr_2']['S']);

        self::assertEquals('Test 3', $item2['Item']['attr_1']['S']);
        self::assertEquals('Test 4', $item2['Item']['attr_2']['S']);

        self::assertEquals('Test 5', $item3['Item']['attr_1']['S']);
        self::assertEquals('Test 6', $item3['Item']['attr_2']['S']);

        // Verify secondary table item
        $secondaryItem = $this->client->getItem([
            'TableName' => 'other_table',
            'Key'       => ['attr_1' => ['S' => 'Other test 3']],
        ]);

        self::assertEquals('Other test 3', $secondaryItem['Item']['attr_1']['S']);
        self::assertEquals('Other test 4', $secondaryItem['Item']['attr_2']['S']);
    }

    public function testLoadDynamoDbFixturesWithAppend(): void
    {
        // First, add an item that should NOT be purged when using append
        $this->client->putItem([
            'TableName' => 'my_table',
            'Item'      => [
                'attr_1' => ['S' => 'item_to_keep'],
            ],
        ]);

        $this->loadDynamoDbFixtures(
            'my_table',
            Options::create()->withAppend(),
            DynamoDbFixture1::class,
            DynamoDbFixture2::class
        );

        // Verify the original item was NOT purged
        $originalItem = $this->client->getItem([
            'TableName' => 'my_table',
            'Key'       => ['attr_1' => ['S' => 'item_to_keep']],
        ]);

        self::assertNotEmpty($originalItem['Item']);
        self::assertEquals('item_to_keep', $originalItem['Item']['attr_1']['S']);

        // Verify fixtures were still loaded
        $item1 = $this->client->getItem([
            'TableName' => 'my_table',
            'Key'       => ['attr_1' => ['S' => 'Test 1']],
        ]);
        $item2 = $this->client->getItem([
            'TableName' => 'my_table',
            'Key'       => ['attr_1' => ['S' => 'Test 3']],
        ]);

        self::assertEquals('Test 1', $item1['Item']['attr_1']['S']);
        self::assertEquals('Test 3', $item2['Item']['attr_1']['S']);
    }

    public function testClearFixtures(): void
    {
        $this->loadDynamoDbFixtures(
            'my_table',
            Options::create(),
            DynamoDbFixture1::class,
            DynamoDbFixture2::class
        );

        $this->clearDynamoDbFixtures('my_table');

        self::assertEmpty($this->getDynamoDbFixtures('my_table'));
    }

    public function testLoadDynamoDbFixturesOnSecondaryTable(): void
    {
        $this->loadDynamoDbFixtures(
            'other_table',
            Options::create(),
            DynamoDbFixture2::class
        );

        $item1 = $this->client->getItem([
            'TableName' => 'other_table',
            'Key'       => ['attr_1' => ['S' => 'Other test 3']],
        ]);

        self::assertEquals('Other test 3', $item1['Item']['attr_1']['S']);
    }

    protected function setUp(): void
    {
        $this->client = $this->getDynamoDbClient();

        // Create tables if they don't exist (for testing purposes)
        $this->createTableIfNotExists('my_table');
        $this->createTableIfNotExists('other_table');

        // Clear tables before each test
        $this->clearTable('my_table');
        $this->clearTable('other_table');
    }

    private function createTableIfNotExists(string $tableName): void
    {
        try {
            $this->client->describeTable(['TableName' => $tableName]);
        } catch (DynamoDbException) {
            // Table doesn't exist, create it
            $this->client->createTable([
                'TableName' => $tableName,
                'KeySchema' => [
                    [
                        'AttributeName' => 'attr_1',
                        'KeyType'       => 'HASH',
                    ],
                ],
                'AttributeDefinitions' => [
                    [
                        'AttributeName' => 'attr_1',
                        'AttributeType' => 'S',
                    ],
                ],
                'BillingMode' => 'PAY_PER_REQUEST',
            ]);

            // Wait for table to be created
            $this->client->waitUntil('TableExists', ['TableName' => $tableName]);
        }
    }

    private function clearTable(string $tableName): void
    {
        try {
            // Scan all items and delete them
            $result = $this->client->scan(['TableName' => $tableName]);

            foreach ($result['Items'] as $item) {
                $this->client->deleteItem([
                    'TableName' => $tableName,
                    'Key'       => ['attr_1' => $item['attr_1']],
                ]);
            }
        } catch (DynamoDbException) {
            // Table might not exist or be empty, ignore
        }
    }
}
