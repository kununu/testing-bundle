<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\App\Fixtures\Connection;

use Doctrine\DBAL\Connection;
use Kununu\DataFixtures\Adapter\ConnectionFixtureInterface;

final class ConnectionFixture6 implements ConnectionFixtureInterface
{
    public function load(Connection $connection): void
    {
        $connection->exec('INSERT INTO `table_3` (`name`, `description`) VALUES (\'my_name\', \'description6\');');
    }
}
