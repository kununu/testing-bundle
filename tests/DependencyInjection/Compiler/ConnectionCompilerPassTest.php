<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\DependencyInjection\Compiler;

use Kununu\DataFixtures\Executor\ConnectionExecutor;
use Kununu\DataFixtures\Purger\ConnectionPurger;
use Kununu\TestingBundle\Command\LoadConnectionFixturesCommand;
use Kununu\TestingBundle\DependencyInjection\Compiler\AbstractConnectionCompilerPass;
use Kununu\TestingBundle\DependencyInjection\Compiler\ConnectionCompilerPass;

final class ConnectionCompilerPassTest extends BaseConnectionCompilerPassTestCase
{
    protected function getCompilerInstance(): AbstractConnectionCompilerPass
    {
        return new ConnectionCompilerPass();
    }

    protected function getSectionName(): string
    {
        return 'connections';
    }

    protected function getPurgerClass(): string
    {
        return ConnectionPurger::class;
    }

    protected function getExecutorClass(): string
    {
        return ConnectionExecutor::class;
    }

    protected function getLoadFixturesCommandClass(): string
    {
        return LoadConnectionFixturesCommand::class;
    }
}
