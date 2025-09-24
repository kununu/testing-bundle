<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Command;

final class LoadDynamoDbFixturesCommand extends LoadFixturesCommand
{
    protected function getFixtureType(): string
    {
        return 'dynamo_db';
    }

    protected function getAliasWord(): string
    {
        return 'DynamoDB alias';
    }
}
