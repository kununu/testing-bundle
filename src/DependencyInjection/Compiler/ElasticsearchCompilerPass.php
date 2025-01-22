<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\DependencyInjection\Compiler;

use Kununu\DataFixtures\Executor\ElasticsearchExecutor;
use Kununu\DataFixtures\Loader\ElasticsearchFixturesLoader;
use Kununu\DataFixtures\Purger\ElasticsearchPurger;
use Kununu\TestingBundle\Command\LoadElasticsearchFixturesCommand;

final class ElasticsearchCompilerPass extends AbstractSearchEngineCompilerPass
{
    protected function getSectionName(): string
    {
        return 'elastic_search';
    }

    protected function getCommandClass(): string
    {
        return LoadElasticsearchFixturesCommand::class;
    }

    protected function getPurgerClass(): string
    {
        return ElasticsearchPurger::class;
    }

    protected function getExecutorClass(): string
    {
        return ElasticsearchExecutor::class;
    }

    protected function getLoaderClass(): string
    {
        return ElasticsearchFixturesLoader::class;
    }
}
