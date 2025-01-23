<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Unit\DependencyInjection\Compiler;

use Kununu\DataFixtures\Executor\OpenSearchExecutor;
use Kununu\DataFixtures\Loader\OpenSearchFixturesLoader;
use Kununu\DataFixtures\Purger\OpenSearchPurger;
use Kununu\TestingBundle\Command\LoadOpenSearchFixturesCommand;
use Kununu\TestingBundle\DependencyInjection\Compiler\AbstractSearchEngineCompilerPass;
use Kununu\TestingBundle\DependencyInjection\Compiler\OpenSearchCompilerPass;

final class OpenSearchCompilerPassTest extends BaseSearchEngineCompilerPassTestCase
{
    protected function getCompilerInstance(): AbstractSearchEngineCompilerPass
    {
        return new OpenSearchCompilerPass();
    }

    protected function getSectionName(): string
    {
        return 'open_search';
    }

    protected function getExecutorClass(): string
    {
        return OpenSearchExecutor::class;
    }

    protected function getLoaderClass(): string
    {
        return OpenSearchFixturesLoader::class;
    }

    protected function getPurgerClass(): string
    {
        return OpenSearchPurger::class;
    }

    protected function getCommandClass(): string
    {
        return LoadOpenSearchFixturesCommand::class;
    }
}
