<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Unit\DependencyInjection\Compiler;

use Kununu\DataFixtures\Executor\OpenSearchExecutor;
use Kununu\DataFixtures\Loader\OpenSearchFixturesLoader;
use Kununu\DataFixtures\Purger\OpenSearchPurger;
use Kununu\TestingBundle\Command\LoadOpenSearchFixturesCommand;
use Kununu\TestingBundle\DependencyInjection\Compiler\AbstractElasticCompilerPass;
use Kununu\TestingBundle\DependencyInjection\Compiler\OpenSearchCompilerPass;

final class OpenSearchCompilerPassTest extends BaseElasticCompilerPassTestCase
{
    protected function getCompilerInstance(): AbstractElasticCompilerPass
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
