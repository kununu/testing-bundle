<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests;

use Doctrine\DBAL\Connection;

/**
 * Trait to add compatibility layer to support both doctrine/dbal ^2.9 and ^3.1 in tests
 */
trait ConnectionHelperTrait
{
    protected function executeQuery(Connection $connection, string $sql): int
    {
        if (method_exists($connection, 'executeStatement')) {
            return $connection->executeStatement($sql);
        }

        return $connection->exec($sql);
    }

    protected function fetchOne(Connection $connection, string $sql)
    {
        if (method_exists($connection, 'fetchOne')) {
            return $connection->fetchOne($sql);
        }

        return $connection->fetchColumn($sql);
    }
}
