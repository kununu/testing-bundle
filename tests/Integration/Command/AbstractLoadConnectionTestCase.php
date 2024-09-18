<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Integration\Command;

use Doctrine\DBAL\Connection;

abstract class AbstractLoadConnectionTestCase extends AbstractFixturesCommandTestCase
{
    private Connection $connection;

    public function testExecuteInteractiveCancelled(): void
    {
        $this->runCommand($this->getExistingCommandAlias(), [], ['interactive' => true], ['no']);

        $expectedRows = [
            [
                'name'        => 'name0',
                'description' => 'description0',
            ],
        ];

        self::assertEquals($expectedRows, $this->connection->fetchAllAssociative('SELECT * FROM `table_1`'));
        self::assertEquals($expectedRows, $this->connection->fetchAllAssociative('SELECT * FROM `table_2`'));
    }

    protected function doAssertionsForExecuteAppend(): void
    {
        self::assertEquals(2, $this->connection->fetchOne('SELECT COUNT(1) FROM `table_1`'));
        self::assertEquals(2, $this->connection->fetchOne('SELECT COUNT(1) FROM `table_2`'));
        self::assertEquals(1, $this->connection->fetchOne('SELECT COUNT(1) FROM `table_1` WHERE `name` = \'name0\''));
        self::assertEquals(1, $this->connection->fetchOne('SELECT COUNT(1) FROM `table_2` WHERE `name` = \'name0\''));
        self::assertEquals(1, $this->connection->fetchOne('SELECT COUNT(1) FROM `table_1` WHERE `name` = \'name3\''));
        self::assertEquals(1, $this->connection->fetchOne('SELECT COUNT(1) FROM `table_2` WHERE `name` = \'name3\''));
    }

    protected function doAssertionsForExecuteNonAppendInteractive(): void
    {
        self::assertEquals(1, $this->connection->fetchOne('SELECT COUNT(1) FROM `table_1`'));
        self::assertEquals(1, $this->connection->fetchOne('SELECT COUNT(1) FROM `table_2`'));
        self::assertEquals(0, $this->connection->fetchOne('select count(1) from table_1 where `name` = \'name0\''));
        self::assertEquals(0, $this->connection->fetchOne('SELECT COUNT(1) FROM `table_2` WHERE `name` = \'name0\''));
        self::assertEquals(1, $this->connection->fetchOne('select count(1) from table_1 where `name` = \'name3\''));
        self::assertEquals(1, $this->connection->fetchOne('SELECT COUNT(1) FROM `table_2` WHERE `name` = \'name3\''));
    }

    protected function doAssertionsForExecuteNonAppendNonInteractive(): void
    {
        self::assertEquals(1, $this->connection->fetchOne('SELECT COUNT(1) FROM `table_1`'));
        self::assertEquals(1, $this->connection->fetchOne('SELECT COUNT(1) FROM `table_2`'));
        self::assertEquals(0, $this->connection->fetchOne('SELECT COUNT(1) FROM `table_1` WHERE `name` = \'name0\''));
        self::assertEquals(0, $this->connection->fetchOne('SELECT COUNT(1) FROM `table_2` WHERE `name` = \'name0\''));
        self::assertEquals(1, $this->connection->fetchOne('SELECT COUNT(1) FROM `table_1` WHERE `name` = \'name3\''));
        self::assertEquals(1, $this->connection->fetchOne('SELECT COUNT(1) FROM `table_2` WHERE `name` = \'name3\''));
    }

    protected function preRunCommand(): void
    {
        $this->connection->executeStatement('TRUNCATE `table_1`');
        $this->connection->executeStatement('TRUNCATE `table_2`');
        $this->connection->executeStatement(
            'INSERT INTO `table_1` (`name`, `description`) VALUES (\'name0\', \'description0\');'
        );
        $this->connection->executeStatement(
            'INSERT INTO `table_2` (`name`, `description`) VALUES (\'name0\', \'description0\');'
        );

        self::assertEquals(1, $this->connection->fetchOne('SELECT COUNT(1) FROM `table_1`'));
        self::assertEquals(1, $this->connection->fetchOne('SELECT COUNT(1) FROM `table_2`'));
        self::assertEquals(1, $this->connection->fetchOne('select count(1) from table_1 where `name` = \'name0\''));
        self::assertEquals(1, $this->connection->fetchOne('select count(1) from table_2 where `name` = \'name0\''));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->getConnection('doctrine.dbal.def_connection');
    }
}
