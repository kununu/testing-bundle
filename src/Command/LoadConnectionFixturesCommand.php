<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Command;

final class LoadConnectionFixturesCommand extends LoadFixturesCommand
{
    protected static function getFixtureType(): string
    {
        return 'connections';
    }
}
