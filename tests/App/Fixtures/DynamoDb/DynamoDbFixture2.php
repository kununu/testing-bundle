<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\App\Fixtures\DynamoDb;

use Kununu\DataFixtures\Adapter\DynamoDb\Record;
use Kununu\DataFixtures\Adapter\DynamoDb\Value;
use Kununu\DataFixtures\Adapter\DynamoDbFixture;

final class DynamoDbFixture2 extends DynamoDbFixture
{
    protected function configure(): void
    {
        $record = new Record([
            Value::stringValue('attr_1', 'Other test 3'),
            Value::stringValue('attr_2', 'Other test 4'),
        ]);

        $this->setTableName('other_table')
            ->addRecord($record);
    }
}
