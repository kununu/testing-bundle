<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Service\SchemaCopy\Adapter;

use Doctrine\DBAL\Connection;
use Kununu\TestingBundle\Service\SchemaCopy\SchemaCopyAdapterInterface;

abstract class AbstractAdapter implements SchemaCopyAdapterInterface
{
    protected const string TYPE = '';

    public function __construct(protected readonly Connection $connection)
    {
    }

    public function runCopy(callable $fn): void
    {
        $this->disableConstraints();
        $fn();
        $this->enableConstraints();
    }

    public function type(): string
    {
        return static::TYPE;
    }

    public function sameTypeAs(SchemaCopyAdapterInterface $other): bool
    {
        return $this->type() === $other->type();
    }

    protected function fetchColumn(Connection $connection, string $sql, int $columnIndex = 0): mixed
    {
        $row = $connection->executeQuery($sql)->fetchNumeric();

        return $row === false ? false : ($row[$columnIndex] ?? false);
    }
}
