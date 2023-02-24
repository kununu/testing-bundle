<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Command;

use Kununu\TestingBundle\Command\LoadConnectionFixturesCommand;

/** @group legacy */
final class LoadConnectionFixturesCommandTest extends AbstractLoadConnectionTestCase
{
    private const COMMAND_1 = 'kununu_testing:load_fixtures:connections:def';
    private const COMMAND_2 = 'kununu_testing:load_fixtures:connections:persistence';
    private const COMMAND_3 = 'kununu_testing:load_fixtures:connections:monolithic';

    protected function getCommandClass(): string
    {
        return LoadConnectionFixturesCommand::class;
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
