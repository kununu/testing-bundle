<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Service\SchemaCopy\Adapter;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Kununu\TestingBundle\Service\SchemaCopy\Adapter\MySqlAdapter;
use Kununu\TestingBundle\Service\SchemaCopy\SchemaCopyAdapterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class MySqlAdapterTest extends TestCase
{
    /** @var string */
    private $executeStatementMethod;
    /** @var Connection|MockObject */
    private $connection;
    /** @var MySqlAdapter */
    private $adapter;

    public function testSameTypeAs(): void
    {
        $this->assertTrue($this->adapter->sameTypeAs($this->adapter));

        $otherAdapter = $this->createMock(SchemaCopyAdapterInterface::class);
        $otherAdapter->expects($this->once())->method('type')->willReturn('IT IS DIFFERENT!');

        $this->assertFalse($this->adapter->sameTypeAs($otherAdapter));
    }

    public function testDoWithoutConstraints(): void
    {
        $this->connection
            ->expects($this->exactly(4))
            ->method($this->executeStatementMethod)
            ->withConsecutive(
                ['SET UNIQUE_CHECKS=0'],
                ['SET FOREIGN_KEY_CHECKS=0'],
                ['SET UNIQUE_CHECKS=1'],
                ['SET FOREIGN_KEY_CHECKS=1']
            )
            ->willReturn(0);

        $value = false;

        $this->adapter->doWithoutConstraints(function() use (&$value): void {
            $value = true;
        });

        $this->assertTrue($value);
    }

    public function testDisableConstraints(): void
    {
        $this->connection
            ->expects($this->exactly(2))
            ->method($this->executeStatementMethod)
            ->withConsecutive(
                ['SET UNIQUE_CHECKS=0'],
                ['SET FOREIGN_KEY_CHECKS=0']
            )
            ->willReturn(0);

        $this->adapter->disableConstraints();
    }

    public function testEnableConstraints(): void
    {
        $this->connection
            ->expects($this->exactly(2))
            ->method($this->executeStatementMethod)
            ->withConsecutive(
                ['SET UNIQUE_CHECKS=1'],
                ['SET FOREIGN_KEY_CHECKS=1']
            )
            ->willReturn(0);

        $this->adapter->enableConstraints();
    }

    protected function fetchColumn(Connection $connection, string $sql, int $columnIndex = 0)
    {
        $result = $connection->executeQuery($sql);

        if (method_exists($result, 'fetchNumeric')) {
            $row = $result->fetchNumeric();

            return $row === false ? false : ($row[$columnIndex] ?? false);
        }

        return $result->fetchColumn($columnIndex);
    }

    protected function setUp(): void
    {
        $this->executeStatementMethod = method_exists(Connection::class, 'executeStatement') ? 'executeStatement' : 'exec';
        $this->fetchNumericMethod = method_exists(Connection::class, 'executeStatement') ? 'executeStatement' : 'exec';

        $this->connection = $this->createMock(Connection::class);
        $this->connection
            ->expects($this->any())
            ->method('getDatabasePlatform')
            ->willReturn($this->createMock(MySqlPlatform::class));

        $this->adapter = new MySqlAdapter($this->connection);
    }
}
