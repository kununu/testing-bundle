<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Unit\Service\SchemaCopy\Copier;

use Doctrine\DBAL\Connection;
use Kununu\TestingBundle\Service\SchemaCopy\Adapter\MySqlAdapter;
use Kununu\TestingBundle\Service\SchemaCopy\Copier\SchemaCopier;
use Kununu\TestingBundle\Service\SchemaCopy\Exception\IncompatibleAdaptersException;
use Kununu\TestingBundle\Service\SchemaCopy\SchemaCopyAdapterFactoryInterface;
use Kununu\TestingBundle\Service\SchemaCopy\SchemaCopyAdapterInterface;
use Kununu\TestingBundle\Service\SchemaCopy\SchemaCopyInterface;
use Kununu\TestingBundle\Tests\Unit\Service\SchemaCopy\SchemaCopyTestCase;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;

final class SchemaCopierTest extends SchemaCopyTestCase
{
    private MockObject|Connection $source;
    private MockObject|Connection $destination;
    private MockObject|SchemaCopyAdapterFactoryInterface $factory;
    private SchemaCopyInterface $copier;

    public function testCopy(): void
    {
        $this->factory
            ->expects($this->exactly(2))
            ->method('createAdapter')
            ->willReturnCallback(
                fn(Connection $connection): SchemaCopyAdapterInterface => match ($connection) {
                    $this->source      => new MySqlAdapter($this->source),
                    $this->destination => new MySqlAdapter($this->destination),
                    default            => throw new LogicException('Unknown connection')
                }
            );

        $this->mockExecuteQuery(
            $this->source,
            [
                $this->createResult('fetchFirstColumn', ['src_table_1', 'src_table_2']),
                $this->createResult(
                    'fetchNumeric',
                    $this->createResultForFetchNumericMethod('[CREATE TABLE src_table_1]')
                ),
                $this->createResult(
                    'fetchNumeric',
                    $this->createResultForFetchNumericMethod('[CREATE TABLE src_table_2]')
                ),
                $this->createResult('fetchFirstColumn', ['src_view_1']),
                $this->createResult(
                    'fetchNumeric',
                    $this->createResultForFetchNumericMethod('[CREATE VIEW src_view_1]')
                ),
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
                $this->createResult('fetchFirstColumn', ['dest_view_1', 'dest_view_2']),
                $this->createResult(
                    'fetchFirstColumn',
                    ['dest_table_1', 'dest_table_2', 'dest_table_3']
                ),
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
        $this->factory
            ->expects($this->exactly(2))
            ->method('createAdapter')
            ->willReturnCallback(
                fn(Connection $connection): SchemaCopyAdapterInterface => match ($connection) {
                    $this->source      => new MySqlAdapter($this->source),
                    $this->destination => $this->createAdapter('YourSql', 2),
                    default            => throw new LogicException('Unknown connection')
                }
            );

        $this->expectException(IncompatibleAdaptersException::class);
        $this->expectExceptionMessage(
            'Source and destination adapters must be of the same type! Source: MySql Destination: YourSql'
        );

        $this->copier->copy($this->source, $this->destination);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->source = $this->createConnection();
        $this->destination = $this->createConnection();
        $this->factory = $this->createMock(SchemaCopyAdapterFactoryInterface::class);
        $this->copier = new SchemaCopier($this->factory);
    }
}
