<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Service\SchemaCopy\Copier;

use Doctrine\DBAL\Connection;
use Kununu\TestingBundle\Service\SchemaCopy\Adapter\MySqlAdapter;
use Kununu\TestingBundle\Service\SchemaCopy\Copier\SchemaCopier;
use Kununu\TestingBundle\Service\SchemaCopy\Exception\IncompatibleAdaptersException;
use Kununu\TestingBundle\Service\SchemaCopy\SchemaCopyAdapterFactoryInterface;
use Kununu\TestingBundle\Tests\Service\SchemaCopy\SchemaCopyTestCase;
use PHPUnit\Framework\MockObject\MockObject;

final class SchemaCopierTest extends SchemaCopyTestCase
{
    /** @var Connection|MockObject */
    private $source;
    /** @var Connection|MockObject */
    private $destination;
    /** @var SchemaCopyAdapterFactoryInterface|MockObject */
    private $factory;
    /** @var SchemaCopier */
    private $copier;

    public function testCopy(): void
    {
        $sourceAdapter = new MySqlAdapter($this->source);
        $destinationAdapter = new MySqlAdapter($this->destination);

        $this->factory
            ->expects($this->exactly(2))
            ->method('createAdapter')
            ->withConsecutive(
                [$this->source],
                [$this->destination]
            )->willReturnOnConsecutiveCalls(
                $sourceAdapter,
                $destinationAdapter
            );

        $this->mockExecuteQuery(
            $this->source,
            [
                $this->createResultMock($this->fetchAllFirstColumnMethod, ['src_table_1', 'src_table_2']),
                $this->createResultMock($this->fetchNumericMethod, $this->createResultForFetchNumericMethod('[CREATE TABLE src_table_1]')),
                $this->createResultMock($this->fetchNumericMethod, $this->createResultForFetchNumericMethod('[CREATE TABLE src_table_2]')),
                $this->createResultMock($this->fetchAllFirstColumnMethod, ['src_view_1']),
                $this->createResultMock($this->fetchNumericMethod, $this->createResultForFetchNumericMethod('[CREATE VIEW src_view_1]')),
            ],
            'SHOW FULL TABLES WHERE Table_type = \'BASE TABLE\'',
            'SHOW CREATE TABLE `src_table_1`',
            'SHOW CREATE TABLE `src_table_2`',
            'SHOW FULL TABLES WHERE Table_type = \'VIEW\'',
            'SHOW CREATE VIEW `src_view_1`'
        );

        $this->mockExecuteQuery(
            $this->destination,
            [
                $this->createResultMock($this->fetchAllFirstColumnMethod, ['dest_view_1', 'dest_view_2']),
                $this->createResultMock($this->fetchAllFirstColumnMethod, ['dest_table_1', 'dest_table_2', 'dest_table_3']),
            ],
            'SHOW FULL TABLES WHERE Table_type = \'VIEW\'',
            'SHOW FULL TABLES WHERE Table_type = \'BASE TABLE\''
        );

        $this->mockExecuteStatement(
            $this->destination,
            'SET UNIQUE_CHECKS=0',
            'SET FOREIGN_KEY_CHECKS=0',
            'DROP VIEW IF EXISTS `dest_view_1`',
            'DROP VIEW IF EXISTS `dest_view_2`',
            'DROP TABLE IF EXISTS `dest_table_1`',
            'DROP TABLE IF EXISTS `dest_table_2`',
            'DROP TABLE IF EXISTS `dest_table_3`',
            '[CREATE TABLE src_table_1]',
            '[CREATE TABLE src_table_2]',
            '[CREATE VIEW src_view_1]',
            'SET UNIQUE_CHECKS=1',
            'SET FOREIGN_KEY_CHECKS=1'
        );

        $this->copier->copy($this->source, $this->destination);
    }

    public function testCopyWithIncompatibleAdapters(): void
    {
        $sourceAdapter = new MySqlAdapter($this->source);
        $destinationAdapter = $this->createAdapterMock('YourSql', 2);

        $this->factory
            ->expects($this->exactly(2))
            ->method('createAdapter')
            ->withConsecutive(
                [$this->source],
                [$this->destination]
            )->willReturnOnConsecutiveCalls(
                $sourceAdapter,
                $destinationAdapter
            );

        $this->expectException(IncompatibleAdaptersException::class);
        $this->expectExceptionMessage('Source and destination adapters must be of the same type! Source: MySql Destination: YourSql');

        $this->copier->copy($this->source, $this->destination);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->source = $this->createConnectionMock();
        $this->destination = $this->createConnectionMock();
        $this->factory = $this->createMock(SchemaCopyAdapterFactoryInterface::class);
        $this->copier = new SchemaCopier($this->factory);
    }
}
