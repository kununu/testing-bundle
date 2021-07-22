<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Traits;

use Doctrine\DBAL\Connection;
use Kununu\DataFixtures\Tools\ConnectionToolsTrait as DataFixturesConnectionToolsTrait;
use PDO;

/**
 * Trait to add compatibility layer to support both doctrine/dbal ^2.9 and ^3.1
 */
trait ConnectionToolsTrait
{
    use DataFixturesConnectionToolsTrait;

    protected function fetchAllRows(Connection $connection, string $sql): array
    {
        $result = $connection->executeQuery($sql);

        if (method_exists($result, 'fetchAllAssociative')) {
            return $result->fetchAllAssociative();
        }

        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function fetchAllFirstColumn(Connection $connection, string $sql): array
    {
        $result = $connection->executeQuery($sql);

        if (method_exists($result, 'fetchFirstColumn')) {
            return $result->fetchFirstColumn();
        }

        return $result->fetchAll(PDO::FETCH_COLUMN);
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

    protected function fetchOne(Connection $connection, string $sql)
    {
        if (method_exists($connection, 'fetchOne')) {
            return $connection->fetchOne($sql);
        }

        return $connection->fetchColumn($sql);
    }
}
