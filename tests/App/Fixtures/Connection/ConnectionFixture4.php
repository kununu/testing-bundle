<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\App\Fixtures\Connection;

use Doctrine\DBAL\Connection;
use Kununu\DataFixtures\Adapter\ConnectionFixtureInterface;

final class ConnectionFixture4 implements ConnectionFixtureInterface
{
    public function load(Connection $connection): void
    {
        $connection->exec('INSERT INTO `table_1` (`name`, `description`) VALUES (\'name4\', \'description4\');');
        $connection->exec('INSERT INTO `table_2` (`name`, `description`) VALUES (\'name4\', \'description4\');');
    }
}
