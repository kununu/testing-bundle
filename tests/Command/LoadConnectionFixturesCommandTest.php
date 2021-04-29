<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Command;

use Doctrine\DBAL\Connection;
use Kununu\TestingBundle\Command\LoadConnectionFixturesCommand;

/**
 * @group integration
 */
final class LoadConnectionFixturesCommandTest extends AbstractFixturesCommandTestCase
{
    private const COMMAND_1 = 'kununu_testing:load_fixtures:connections:def';
    private const COMMAND_2 = 'kununu_testing:load_fixtures:connections:persistence';
    private const COMMAND_3 = 'kununu_testing:load_fixtures:connections:monolithic';

    /** @var Connection */
    private $defConnection;

    protected function doAssertionsForExecuteAppend(): void
    {
        $this->assertEquals(2, $this->defConnection->executeQuery('select count(1) from table_1')->fetchOne());
        $this->assertEquals(2, $this->defConnection->executeQuery('select count(1) from table_2')->fetchOne());
        $this->assertEquals(1, $this->defConnection->executeQuery('select count(1) from table_1 where `name` = \'name0\'')->fetchOne());
        $this->assertEquals(1, $this->defConnection->executeQuery('select count(1) from table_2 where `name` = \'name0\'')->fetchOne());
        $this->assertEquals(1, $this->defConnection->executeQuery('select count(1) from table_1 where `name` = \'name3\'')->fetchOne());
        $this->assertEquals(1, $this->defConnection->executeQuery('select count(1) from table_2 where `name` = \'name3\'')->fetchOne());
    }

    protected function doAssertionsForExecuteNonAppendInteractive(): void
    {
        $this->assertEquals(1, $this->defConnection->executeQuery('select count(1) from table_1')->fetchOne());
        $this->assertEquals(1, $this->defConnection->executeQuery('select count(1) from table_2')->fetchOne());
        $this->assertEquals(0, $this->defConnection->executeQuery('select count(1) from table_1 where `name` = \'name0\'')->fetchOne());
        $this->assertEquals(0, $this->defConnection->executeQuery('select count(1) from table_2 where `name` = \'name0\'')->fetchOne());
        $this->assertEquals(1, $this->defConnection->executeQuery('select count(1) from table_1 where `name` = \'name3\'')->fetchOne());
        $this->assertEquals(1, $this->defConnection->executeQuery('select count(1) from table_2 where `name` = \'name3\'')->fetchOne());
    }

    protected function doAssertionsForExecuteNonAppendNonInteractive(): void
    {
        $this->assertEquals(1, $this->defConnection->executeQuery('select count(1) from table_1')->fetchOne());
        $this->assertEquals(1, $this->defConnection->executeQuery('select count(1) from table_2')->fetchOne());
        $this->assertEquals(0, $this->defConnection->executeQuery('select count(1) from table_1 where `name` = \'name0\'')->fetchOne());
        $this->assertEquals(0, $this->defConnection->executeQuery('select count(1) from table_2 where `name` = \'name0\'')->fetchOne());
        $this->assertEquals(1, $this->defConnection->executeQuery('select count(1) from table_1 where `name` = \'name3\'')->fetchOne());
        $this->assertEquals(1, $this->defConnection->executeQuery('select count(1) from table_2 where `name` = \'name3\'')->fetchOne());
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
        $this->defConnection->executeStatement('TRUNCATE `table_1`');
        $this->defConnection->executeStatement('TRUNCATE `table_2`');
        $this->defConnection->executeStatement('INSERT INTO `table_1` (`name`, `description`) VALUES (\'name0\', \'description0\');');
        $this->defConnection->executeStatement('INSERT INTO `table_2` (`name`, `description`) VALUES (\'name0\', \'description0\');');

        $this->assertEquals(1, $this->defConnection->executeQuery('select count(1) from table_1')->fetchOne());
        $this->assertEquals(1, $this->defConnection->executeQuery('select count(1) from table_2')->fetchOne());
        $this->assertEquals(1, $this->defConnection->executeQuery('select count(1) from table_1 where `name` = \'name0\'')->fetchOne());
        $this->assertEquals(1, $this->defConnection->executeQuery('select count(1) from table_2 where `name` = \'name0\'')->fetchOne());
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->defConnection = self::$container->get('doctrine.dbal.def_connection');
        var_dump($this->defConnection->getHost());
        var_dump($this->defConnection->getParams());
        var_dump($this->defConnection->getPort());
        var_dump($this->defConnection->getUsername());
    }
}
