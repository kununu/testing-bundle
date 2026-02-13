<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\App\Fixtures\Connection;

use Doctrine\DBAL\Connection;
use Kununu\DataFixtures\Adapter\ConnectionFixtureInterface;

final readonly class ConnectionFixture6 implements ConnectionFixtureInterface
{
    public function load(Connection $connection): void
    {
        $connection->executeStatement(
            <<<'SQL'
INSERT INTO `table_3` (`name`, `description`) VALUES ('my_name', 'description6');
SQL
        );
    }
}
