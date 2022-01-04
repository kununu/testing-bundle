<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Command;

final class LoadNonTransactionalConnectionFixturesCommand extends LoadFixturesCommand
{
    protected function getFixtureType(): string
    {
        return 'non_transactional_connections';
    }

    protected function getAliasWord(): string
    {
        return 'Database Connection';
    }
}
