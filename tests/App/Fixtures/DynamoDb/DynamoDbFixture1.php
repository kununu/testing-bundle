<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\App\Fixtures\DynamoDb;

use Kununu\DataFixtures\Adapter\DynamoDb\Record;
use Kununu\DataFixtures\Adapter\DynamoDb\Value;
use Kununu\DataFixtures\Adapter\DynamoDbFixture;

final class DynamoDbFixture1 extends DynamoDbFixture
{
    protected function configure(): void
    {
        $records = [
            new Record([
                Value::stringValue('attr_1', 'Test 1'),
                Value::stringValue('attr_2', 'Test 2'),
            ]),
            new Record([
                Value::stringValue('attr_1', 'Test 3'),
                Value::stringValue('attr_2', 'Test 4'),
            ]),
            new Record([
                Value::stringValue('attr_1', 'Test 5'),
                Value::stringValue('attr_2', 'Test 6'),
            ]),
        ];

        $this->setTableName('my_table')
            ->addRecords($records);
    }
}
