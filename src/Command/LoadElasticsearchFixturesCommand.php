<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Command;

final class LoadElasticsearchFixturesCommand extends LoadFixturesCommand
{
    protected static function getFixtureType(): string
    {
        return 'elastic_search';
    }
}
