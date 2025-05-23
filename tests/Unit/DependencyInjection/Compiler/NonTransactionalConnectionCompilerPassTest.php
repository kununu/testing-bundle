<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Unit\DependencyInjection\Compiler;

use Kununu\DataFixtures\Executor\NonTransactionalConnectionExecutor;
use Kununu\DataFixtures\Purger\NonTransactionalConnectionPurger;
use Kununu\TestingBundle\Command\LoadNonTransactionalConnectionFixturesCommand;
use Kununu\TestingBundle\DependencyInjection\Compiler\AbstractConnectionCompilerPass;
use Kununu\TestingBundle\DependencyInjection\Compiler\NonTransactionalConnectionCompilerPass;

final class NonTransactionalConnectionCompilerPassTest extends BaseConnectionCompilerPassTestCase
{
    protected function getCompilerInstance(): AbstractConnectionCompilerPass
    {
        return new NonTransactionalConnectionCompilerPass();
    }

    protected function getSectionName(): string
    {
        return 'non_transactional_connections';
    }

    protected function getExecutorClass(): string
    {
        return NonTransactionalConnectionExecutor::class;
    }

    protected function getPurgerClass(): string
    {
        return NonTransactionalConnectionPurger::class;
    }

    protected function getCommandClass(): string
    {
        return LoadNonTransactionalConnectionFixturesCommand::class;
    }
}
