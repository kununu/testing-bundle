<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Command;

final class LoadElasticsearchFixturesCommand extends LoadFixturesCommand
{
    protected function getFixtureType(): string
    {
        return 'elastic_search';
    }

    protected function getAliasWord(): string
    {
        return 'Elasticsearch Index alias';
    }
}
