<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Table;
use ReflectionClass;

trait StorageSetupTrait
{
    private $useDbal3 = null;

    protected function recreateConnectionDatabase(Connection $connection, string $databaseName, Table ...$tables): void
    {
        // Support for DBAL 3.x
        if ($this->useDbal3()) {
            $schemaManager = $connection->createSchemaManager();
            $schemaManager->dropDatabase($databaseName);
            $schemaManager->createDatabase($databaseName);
        } else {
            ($schemaManager = $connection->getSchemaManager())->dropAndCreateDatabase($databaseName);
        }

        foreach ($tables as $table) {
            $schemaManager->createTable($table);
        }
    }

    protected function insertData(Connection $connection, string ...$inserts): void
    {
        foreach ($inserts as $insert) {
            $connection->executeStatement($insert);
        }
    }

    private function useDbal3(): bool
    {
        if (null === $this->useDbal3) {
            $reflectionClass = new ReflectionClass(Connection::class);
            $this->useDbal3 = $reflectionClass->hasMethod('createSchemaManager');
        }

        return $this->useDbal3;
    }
}
