<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\DependencyInjection\Compiler;

use Kununu\DataFixtures\Executor\OpenSearchExecutor;
use Kununu\DataFixtures\Loader\OpenSearchFixturesLoader;
use Kununu\DataFixtures\Purger\OpenSearchPurger;
use Kununu\TestingBundle\Command\LoadOpenSearchFixturesCommand;

final class OpenSearchCompilerPass extends AbstractSearchEngineCompilerPass
{
    protected function getSectionName(): string
    {
        return 'open_search';
    }

    protected function getCommandClass(): string
    {
        return LoadOpenSearchFixturesCommand::class;
    }

    protected function getPurgerClass(): string
    {
        return OpenSearchPurger::class;
    }

    protected function getExecutorClass(): string
    {
        return OpenSearchExecutor::class;
    }

    protected function getLoaderClass(): string
    {
        return OpenSearchFixturesLoader::class;
    }
}
