<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Unit\Service\SchemaCopy;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySQL80Platform;
use Doctrine\DBAL\Result;
use Kununu\TestingBundle\Service\SchemaCopy\SchemaCopyAdapterInterface;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class SchemaCopyTestCase extends TestCase
{
    protected string $fetchAllFirstColumnMethod;

    protected function createAdapter(string $type, int $expectedCalls = 1): MockObject&SchemaCopyAdapterInterface
    {
        $adapter = $this->createMock(SchemaCopyAdapterInterface::class);
        $adapter
            ->expects(self::exactly($expectedCalls))
            ->method('type')
            ->willReturn($type);

        return $adapter;
    }

    protected function createConnection(): MockObject&Connection
    {
        $connection = $this->createMock(Connection::class);
        $connection
            ->expects(self::any())
            ->method('getDatabasePlatform')
            ->willReturn($this->createMock(MySQL80Platform::class));

        return $connection;
    }

    protected function mockExecuteQuery(MockObject&Connection $connection, array $results, string ...$statements): void
    {
        $map = array_combine($statements, $results);

        $connection
            ->expects(self::exactly(count($statements)))
            ->method('executeQuery')
            ->willReturnCallback(fn(string $statement): Result => match (true) {
                isset($map[$statement]) => $map[$statement],
                default                 => throw new LogicException(sprintf('Statement "%s" not found', $statement)),
            });
    }

    protected function mockExecuteStatement(MockObject&Connection $connection, string ...$statements): void
    {
        $map = array_combine($statements, array_fill(0, count($statements), 0));

        $connection
            ->expects(self::exactly(count($statements)))
            ->method('executeStatement')
            ->willReturnCallback(fn(string $statement): int => match (true) {
                isset($map[$statement]) => $map[$statement],
                default                 => throw new LogicException(sprintf('Statement "%s" not found', $statement)),
            });
    }

    protected function createResult(string $method, mixed $return): MockObject&Result
    {
        $result = $this->createMock(Result::class);
        $result
            ->expects(self::once())
            ->method($method)
            ->withAnyParameters()
            ->willReturn($return);

        return $result;
    }

    protected function createResultForFetchNumericMethod(mixed $result): array
    {
        return [0 => null, 1 => $result];
    }
}
