<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\DependencyInjection\Compiler;

use Kununu\DataFixtures\Executor\ConnectionExecutor;
use Kununu\DataFixtures\Purger\ConnectionPurger;
use Kununu\TestingBundle\Command\LoadConnectionFixturesCommand;

final class ConnectionCompilerPass extends AbstractConnectionCompilerPass
{
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

    protected function getCommandClass(): string
    {
        return LoadConnectionFixturesCommand::class;
    }
}
