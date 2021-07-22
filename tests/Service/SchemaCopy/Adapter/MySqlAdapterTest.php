<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Service\SchemaCopy\Adapter;

use Doctrine\DBAL\Connection;
use Kununu\TestingBundle\Service\SchemaCopy\Adapter\MySqlAdapter;
use Kununu\TestingBundle\Tests\Service\SchemaCopy\SchemaCopyTestCase;
use PHPUnit\Framework\MockObject\MockObject;

final class MySqlAdapterTest extends SchemaCopyTestCase
{
    /** @var Connection|MockObject */
    private $connection;
    /** @var MySqlAdapter */
    private $adapter;

    public function testSameTypeAsEqual(): void
    {
        $this->assertTrue($this->adapter->sameTypeAs($this->adapter));
    }

    public function testSameTypeAsNotEqual(): void
    {
        $this->assertFalse($this->adapter->sameTypeAs($this->createAdapterMock('IT IS DIFFERENT!')));
    }

    public function testRunCopy(): void
    {
        $this->mockExecuteStatement(
            $this->connection,
            'SET UNIQUE_CHECKS=0',
            'SET FOREIGN_KEY_CHECKS=0',
            'SET UNIQUE_CHECKS=1',
            'SET FOREIGN_KEY_CHECKS=1'
        );

        $value = false;

        $this->adapter->runCopy(function() use (&$value): void {
            $value = true;
        });

        $this->assertTrue($value);
    }

    public function testDisableConstraints(): void
    {
        $this->mockExecuteStatement(
            $this->connection,
            'SET UNIQUE_CHECKS=0',
            'SET FOREIGN_KEY_CHECKS=0'
        );

        $this->adapter->disableConstraints();
    }

    public function testEnableConstraints(): void
    {
        $this->mockExecuteStatement(
            $this->connection,
            'SET UNIQUE_CHECKS=1',
            'SET FOREIGN_KEY_CHECKS=1'
        );

        $this->adapter->enableConstraints();
    }

    public function testGetTableCreateStatement(): void
    {
        $this->mockExecuteQuery(
            $this->connection,
            [
                $this->createResultMock($this->fetchNumericMethod, false),
            ],
            'SHOW CREATE TABLE `my_table`'
        );

        $this->adapter->getTableCreateStatement('my_table');
    }

    public function testGetTables(): void
    {
        $this->mockExecuteQuery(
            $this->connection,
            [
                $this->createResultMock($this->fetchAllFirstColumnMethod, []),
            ],
            'SHOW FULL TABLES WHERE Table_type = \'BASE TABLE\''
        );

        $this->adapter->getTables();
    }

    public function testGetViewCreateStatement(): void
    {
        $this->mockExecuteQuery(
            $this->connection,
            [
                $this->createResultMock($this->fetchNumericMethod, false),
            ],
            'SHOW CREATE VIEW `my_view`'
        );

        $this->adapter->getViewCreateStatement('my_view');
    }

    public function testGetViews(): void
    {
        $this->mockExecuteQuery(
            $this->connection,
            [
                $this->createResultMock($this->fetchAllFirstColumnMethod, []),
            ],
            'SHOW FULL TABLES WHERE Table_type = \'VIEW\''
        );

        $this->adapter->getViews();
    }

    public function testPurgeTablesAndViews(): void
    {
        $this->mockExecuteQuery(
            $this->connection,
            [
                $this->createResultMock($this->fetchAllFirstColumnMethod, ['view_1', 'view_2']),
                $this->createResultMock($this->fetchAllFirstColumnMethod, ['table_1', 'table_2', 'table_3']),
            ],
            'SHOW FULL TABLES WHERE Table_type = \'VIEW\'',
            'SHOW FULL TABLES WHERE Table_type = \'BASE TABLE\''
        );

        $this->mockExecuteStatement(
            $this->connection,
            'DROP VIEW IF EXISTS `view_1`',
            'DROP VIEW IF EXISTS `view_2`',
            'DROP TABLE IF EXISTS `table_1`',
            'DROP TABLE IF EXISTS `table_2`',
            'DROP TABLE IF EXISTS `table_3`'
        );

        $this->adapter->purgeTablesAndViews();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->createConnectionMock();
        $this->adapter = new MySqlAdapter($this->connection);
    }
}
