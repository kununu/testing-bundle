<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Command;

final class LoadCacheFixturesCommand extends LoadFixturesCommand
{
    protected function getFixtureType(): string
    {
        return 'cache_pools';
    }

    protected function getAliasWord(): string
    {
        return 'Cache Pool';
    }
}
