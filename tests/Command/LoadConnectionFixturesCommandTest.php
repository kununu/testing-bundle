<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Command;

use Doctrine\DBAL\Connection;
use Kununu\TestingBundle\Command\LoadConnectionFixturesCommand;
use Kununu\TestingBundle\Traits\ConnectionToolsTrait;

final class LoadConnectionFixturesCommandTest extends AbstractFixturesCommandTestCase
{
    use ConnectionToolsTrait;

    private const COMMAND_1 = 'kununu_testing:load_fixtures:connections:def';
    private const COMMAND_2 = 'kununu_testing:load_fixtures:connections:persistence';
    private const COMMAND_3 = 'kununu_testing:load_fixtures:connections:monolithic';

    /** @var Connection */
    private $connection;

    protected function doAssertionsForExecuteAppend(): void
    {
        $this->assertEquals(2, $this->fetchOne($this->connection, 'select count(1) from table_1'));
        $this->assertEquals(2, $this->fetchOne($this->connection, 'select count(1) from table_2'));
        $this->assertEquals(1, $this->fetchOne($this->connection, 'select count(1) from table_1 where `name` = \'name0\''));
        $this->assertEquals(1, $this->fetchOne($this->connection, 'select count(1) from table_2 where `name` = \'name0\''));
        $this->assertEquals(1, $this->fetchOne($this->connection, 'select count(1) from table_1 where `name` = \'name3\''));
        $this->assertEquals(1, $this->fetchOne($this->connection, 'select count(1) from table_2 where `name` = \'name3\''));
    }

    protected function doAssertionsForExecuteNonAppendInteractive(): void
    {
        $this->assertEquals(1, $this->fetchOne($this->connection, 'select count(1) from table_1'));
        $this->assertEquals(1, $this->fetchOne($this->connection, 'select count(1) from table_2'));
        $this->assertEquals(0, $this->fetchOne($this->connection, 'select count(1) from table_1 where `name` = \'name0\''));
        $this->assertEquals(0, $this->fetchOne($this->connection, 'select count(1) from table_2 where `name` = \'name0\''));
        $this->assertEquals(1, $this->fetchOne($this->connection, 'select count(1) from table_1 where `name` = \'name3\''));
        $this->assertEquals(1, $this->fetchOne($this->connection, 'select count(1) from table_2 where `name` = \'name3\''));
    }

    protected function doAssertionsForExecuteNonAppendNonInteractive(): void
    {
        $this->assertEquals(1, $this->fetchOne($this->connection, 'select count(1) from table_1'));
        $this->assertEquals(1, $this->fetchOne($this->connection, 'select count(1) from table_2'));
        $this->assertEquals(0, $this->fetchOne($this->connection, 'select count(1) from table_1 where `name` = \'name0\''));
        $this->assertEquals(0, $this->fetchOne($this->connection, 'select count(1) from table_2 where `name` = \'name0\''));
        $this->assertEquals(1, $this->fetchOne($this->connection, 'select count(1) from table_1 where `name` = \'name3\''));
        $this->assertEquals(1, $this->fetchOne($this->connection, 'select count(1) from table_2 where `name` = \'name3\''));
    }

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

    protected function preRunCommand(): void
    {
        $this->executeQuery($this->connection, 'TRUNCATE `table_1`');
        $this->executeQuery($this->connection, 'TRUNCATE `table_2`');
        $this->executeQuery($this->connection, 'INSERT INTO `table_1` (`name`, `description`) VALUES (\'name0\', \'description0\');');
        $this->executeQuery($this->connection, 'INSERT INTO `table_2` (`name`, `description`) VALUES (\'name0\', \'description0\');');

        $this->assertEquals(1, $this->fetchOne($this->connection, 'select count(1) from table_1'));
        $this->assertEquals(1, $this->fetchOne($this->connection, 'select count(1) from table_2'));
        $this->assertEquals(1, $this->fetchOne($this->connection, 'select count(1) from table_1 where `name` = \'name0\''));
        $this->assertEquals(1, $this->fetchOne($this->connection, 'select count(1) from table_2 where `name` = \'name0\''));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = self::$container->get('doctrine.dbal.def_connection');
    }
}
