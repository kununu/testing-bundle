<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Command;

use Kununu\TestingBundle\Command\LoadNonTransactionalConnectionFixturesCommand;

/** @group legacy */
final class LoadNonTransactionalConnectionFixturesCommandTest extends AbstractLoadConnectionTestCase
{
    private const COMMAND_1 = 'kununu_testing:load_fixtures:non_transactional_connections:def';
    private const COMMAND_2 = 'kununu_testing:load_fixtures:non_transactional_connections:persistence';
    private const COMMAND_3 = 'kununu_testing:load_fixtures:non_transactional_connections:monolithic';

    protected function getCommandClass(): string
    {
        return LoadNonTransactionalConnectionFixturesCommand::class;
    }

    protected function getExistingCommandAlias(): string
    {
        return self::COMMAND_1;
    }

    protected function getNonExistingCommandAliases(): array
    {
        return [self::COMMAND_2, self::COMMAND_3];
    }
}
