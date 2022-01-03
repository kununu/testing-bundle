<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\DependencyInjection\Compiler;

use Kununu\DataFixtures\Executor\ConnectionExecutor;
use Kununu\DataFixtures\Purger\ConnectionPurger;

final class ConnectionCompilerPass extends AbstractConnectionCompilerPass
{
    protected function getSectionName(): string
    {
        return 'connections';
    }

    protected function getConnectionPurgerClass(): string
    {
        return ConnectionPurger::class;
    }

    protected function getConnectionExecutorClass(): string
    {
        return ConnectionExecutor::class;
    }
}
