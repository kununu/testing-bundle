<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Command;

final class LoadOpenSearchFixturesCommand extends LoadFixturesCommand
{
    protected function getFixtureType(): string
    {
        return 'open_search';
    }

    protected function getAliasWord(): string
    {
        return 'OpenSearch Index alias';
    }
}
