<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Command;

final class LoadConnectionFixturesCommand extends LoadFixturesCommand
{
    protected function getFixtureType(): string
    {
        return 'connections';
    }

    protected function getAliasWord(): string
    {
        return 'Database Connection';
    }
}
