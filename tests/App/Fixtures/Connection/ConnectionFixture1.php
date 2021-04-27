<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\App\Fixtures\Connection;

use Doctrine\DBAL\Connection;
use Kununu\DataFixtures\Adapter\ConnectionFixtureInterface;
use Kununu\DataFixtures\InitializableFixtureInterface;

final class ConnectionFixture1 implements ConnectionFixtureInterface, InitializableFixtureInterface
{
    private $arg1;
    private $arg2 = false;

    public function load(Connection $connection): void
    {
        $connection->executeStatement('INSERT INTO `table_1` (`name`, `description`) VALUES (\'name\', \'description\');');
        $connection->executeStatement('INSERT INTO `table_2` (`name`, `description`) VALUES (\'name\', \'description\');');
    }

    public function initializeFixture(...$args): void
    {
        foreach ($args as $index => $arg) {
            if (0 === $index && is_string($arg)) {
                $this->arg1 = $arg;
            }

            if (1 === $index && is_bool($arg)) {
                $this->arg2 = $arg;
            }
        }
    }

    public function arg1(): ?string
    {
        return $this->arg1;
    }

    public function arg2(): bool
    {
        return $this->arg2;
    }
}
