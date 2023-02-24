<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Service\SchemaCopy;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Result;
use Kununu\TestingBundle\Service\SchemaCopy\SchemaCopyAdapterInterface;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class SchemaCopyTestCase extends TestCase
{
    protected string $executeStatementMethod;
    protected string $fetchNumericMethod;
    protected string $fetchAllFirstColumnMethod;

    protected function createAdapterMock(string $type, int $expectedCalls = 1): SchemaCopyAdapterInterface
    {
        $mock = $this->createMock(SchemaCopyAdapterInterface::class);
        $mock
            ->expects($this->exactly($expectedCalls))
            ->method('type')
            ->willReturn($type);

        return $mock;
    }

    protected function createConnectionMock(): MockObject|Connection
    {
        $mock = $this->createMock(Connection::class);
        $mock
            ->expects($this->any())
            ->method('getDatabasePlatform')
            ->willReturn($this->createMock(MySqlPlatform::class));

        return $mock;
    }

    protected function mockExecuteQuery(MockObject $connection, array $results, string ...$statements): void
    {
        $map = array_combine($statements, $results);

        $connection
            ->expects($this->exactly(count($statements)))
            ->method('executeQuery')
            ->willReturnCallback(fn(string $statement): Result => match (true) {
                isset($map[$statement]) => $map[$statement],
                default                 => throw new LogicException(sprintf('Statement "%s" not found', $statement))
            });
    }

    protected function mockExecuteStatement(MockObject $connection, string ...$statements): void
    {
        $map = array_combine($statements, array_fill(0, count($statements), 0));

        $connection
            ->expects($this->exactly(count($statements)))
            ->method($this->executeStatementMethod)
            ->willReturnCallback(fn(string $statement): int => match (true) {
                isset($map[$statement]) => $map[$statement],
                default                 => throw new LogicException(sprintf('Statement "%s" not found', $statement))
            });
    }

    protected function createResultMock(string $method, mixed $result): Result
    {
        $mock = $this->createMock(Result::class);
        $mock
            ->expects($this->once())
            ->method($method)
            ->withAnyParameters()
            ->willReturn($result);

        return $mock;
    }

    protected function createResultForFetchNumericMethod(mixed $result): mixed
    {
        if ('fetchNumeric' === $this->fetchNumericMethod) {
            return [0 => null, 1 => $result];
        }

        return $result;
    }

    protected function setUp(): void
    {
        $this->executeStatementMethod = method_exists(Connection::class, 'executeStatement')
            ? 'executeStatement'
            : 'exec';
        $this->fetchNumericMethod = method_exists(Result::class, 'fetchNumeric') ? 'fetchNumeric' : 'fetchColumn';
        $this->fetchAllFirstColumnMethod = method_exists(Result::class, 'fetchFirstColumn')
            ? 'fetchFirstColumn'
            : 'fetchAll';
    }
}
