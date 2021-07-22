<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Service\SchemaCopy\Factory;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Kununu\TestingBundle\Service\SchemaCopy\Adapter\MySqlAdapter;
use Kununu\TestingBundle\Service\SchemaCopy\Exception\UnsupportedDatabasePlatformException;
use Kununu\TestingBundle\Service\SchemaCopy\SchemaCopyAdapterFactoryInterface;
use Kununu\TestingBundle\Service\SchemaCopy\SchemaCopyAdapterInterface;

final class AdapterFactory implements SchemaCopyAdapterFactoryInterface
{
    public function createAdapter(Connection $connection): SchemaCopyAdapterInterface
    {
        $databasePlatform = $connection->getDatabasePlatform();

        if ($databasePlatform instanceof MySqlPlatform) {
            return new MySqlAdapter($connection);
        }

        throw new UnsupportedDatabasePlatformException($databasePlatform);
    }
}
