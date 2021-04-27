<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Command;

final class LoadCacheFixturesCommand extends LoadFixturesCommand
{
    protected static function getFixtureType(): string
    {
        return 'cache_pools';
    }
}
