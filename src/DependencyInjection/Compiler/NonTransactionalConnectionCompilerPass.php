<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\DependencyInjection\Compiler;

use Kununu\DataFixtures\Executor\NonTransactionalConnectionExecutor;
use Kununu\DataFixtures\Purger\NonTransactionalConnectionPurger;
use Kununu\TestingBundle\Command\LoadNonTransactionalConnectionFixturesCommand;

final class NonTransactionalConnectionCompilerPass extends AbstractConnectionCompilerPass
{
    protected function getSectionName(): string
    {
        return 'non_transactional_connections';
    }

    protected function getPurgerClass(): string
    {
        return NonTransactionalConnectionPurger::class;
    }

    protected function getExecutorClass(): string
    {
        return NonTransactionalConnectionExecutor::class;
    }

    protected function getCommandClass(): string
    {
        return LoadNonTransactionalConnectionFixturesCommand::class;
    }
}
