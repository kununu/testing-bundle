<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Command;

use Doctrine\DBAL\Connection;
use Kununu\TestingBundle\Traits\ConnectionToolsTrait;

abstract class AbstractLoadConnectionTestCase extends AbstractFixturesCommandTestCase
{
    use ConnectionToolsTrait;

    /** @var Connection */
    private $connection;

    public function testExecuteInteractiveCancelled(): void
    {
        $this->runCommand($this->getExistingCommandAlias(), [], ['interactive' => true], ['no']);

        $expectedRows = [
            [
                'name'        => 'name0',
                'description' => 'description0',
            ],
        ];

        $this->assertEquals($expectedRows, $this->fetchAllRows($this->connection, 'SELECT * FROM `table_1`'));
        $this->assertEquals($expectedRows, $this->fetchAllRows($this->connection, 'SELECT * FROM `table_2`'));
    }

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
        $this->connection = $this->getFixturesContainer()->get('doctrine.dbal.def_connection');
    }
}
