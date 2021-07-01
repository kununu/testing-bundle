<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Service\SchemaCopy;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Result;
use Kununu\TestingBundle\Service\SchemaCopy\SchemaCopyAdapterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class SchemaCopyTestCase extends TestCase
{
    /** @var string */
    protected $executeStatementMethod;
    /** @var string */
    protected $fetchNumericMethod;
    /** @var string */
    protected $fetchAllFirstColumnMethod;

    /**
     * @param string $type
     * @param int    $expectedCalls
     *
     * @return SchemaCopyAdapterInterface|MockObject
     */
    protected function createAdapterMock(string $type, int $expectedCalls = 1): SchemaCopyAdapterInterface
    {
        $mock = $this->createMock(SchemaCopyAdapterInterface::class);
        $mock
            ->expects($this->exactly($expectedCalls))
            ->method('type')
            ->willReturn($type);

        return $mock;
    }

    /**
     * @return Connection|MockObject
     */
    protected function createConnectionMock(): Connection
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
        $connection
            ->expects($this->exactly(count($statements)))
            ->method('executeQuery')
            ->withConsecutive(...$this->createConsecutiveStatements($statements))
            ->willReturnOnConsecutiveCalls(...$results);
    }

    protected function mockExecuteStatement(MockObject $connection, string ...$statements): void
    {
        $connection
            ->expects($this->exactly(count($statements)))
            ->method($this->executeStatementMethod)
            ->withConsecutive(...$this->createConsecutiveStatements($statements))
            ->willReturn(0);
    }

    /**
     * @param string $method
     * @param mixed  $result
     *
     * @return Result
     */
    protected function createResultMock(string $method, $result): Result
    {
        $mock = $this->createMock(Result::class);
        $mock
            ->expects($this->once())
            ->method($method)
            ->withAnyParameters()
            ->willReturn($result);

        return $mock;
    }

    /**
     * @param mixed $result
     *
     * @return mixed|array
     */
    protected function createResultForFetchNumericMethod($result)
    {
        if ('fetchNumeric' === $this->fetchNumericMethod) {
            return [0 => null, 1 => $result];
        }

        return $result;
    }

    protected function setUp(): void
    {
        $this->executeStatementMethod = method_exists(Connection::class, 'executeStatement') ? 'executeStatement' : 'exec';
        $this->fetchNumericMethod = method_exists(Result::class, 'fetchNumeric') ? 'fetchNumeric' : 'fetchColumn';
        $this->fetchAllFirstColumnMethod = method_exists(Result::class, 'fetchFirstColumn') ? 'fetchFirstColumn' : 'fetchAll';
    }

    private function createConsecutiveStatements(array $statements): array
    {
        return array_map(
            function(string $statement): array {
                return [$statement];
            },
            $statements
        );
    }
}
