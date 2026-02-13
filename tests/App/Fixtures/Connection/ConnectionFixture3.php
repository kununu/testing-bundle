<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\App\Fixtures\Connection;

use Doctrine\DBAL\Connection;
use Kununu\DataFixtures\Adapter\ConnectionFixtureInterface;

final readonly class ConnectionFixture3 implements ConnectionFixtureInterface
{
    public function load(Connection $connection): void
    {
        $connection->executeStatement(
            <<<'SQL'
INSERT INTO `table_1` (`name`, `description`) VALUES ('name3', 'description3');
SQL
        );
        $connection->executeStatement(
            <<<'SQL'
INSERT INTO `table_2` (`name`, `description`) VALUES ('name3', 'description3');
SQL
        );
    }
}
