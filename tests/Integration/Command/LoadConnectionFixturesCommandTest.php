<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Integration\Command;

use Kununu\TestingBundle\Command\LoadConnectionFixturesCommand;

final class LoadConnectionFixturesCommandTest extends AbstractLoadConnectionTestCase
{
    private const string COMMAND_1 = 'kununu_testing:load_fixtures:connections:def';
    private const string COMMAND_2 = 'kununu_testing:load_fixtures:connections:persistence';
    private const string COMMAND_3 = 'kununu_testing:load_fixtures:connections:monolithic';

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
