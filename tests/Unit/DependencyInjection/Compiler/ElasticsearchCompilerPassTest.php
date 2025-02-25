<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Unit\DependencyInjection\Compiler;

use Kununu\DataFixtures\Executor\ElasticsearchExecutor;
use Kununu\DataFixtures\Loader\ElasticsearchFixturesLoader;
use Kununu\DataFixtures\Purger\ElasticsearchPurger;
use Kununu\TestingBundle\Command\LoadElasticsearchFixturesCommand;
use Kununu\TestingBundle\DependencyInjection\Compiler\AbstractSearchEngineCompilerPass;
use Kununu\TestingBundle\DependencyInjection\Compiler\ElasticsearchCompilerPass;

final class ElasticsearchCompilerPassTest extends BaseSearchEngineCompilerPassTestCase
{
    protected function getCompilerInstance(): AbstractSearchEngineCompilerPass
    {
        return new ElasticsearchCompilerPass();
    }

    protected function getSectionName(): string
    {
        return 'elastic_search';
    }

    protected function getExecutorClass(): string
    {
        return ElasticsearchExecutor::class;
    }

    protected function getLoaderClass(): string
    {
        return ElasticsearchFixturesLoader::class;
    }

    protected function getPurgerClass(): string
    {
        return ElasticsearchPurger::class;
    }

    protected function getCommandClass(): string
    {
        return LoadElasticsearchFixturesCommand::class;
    }
}
