<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\App\Fixtures\Connection;

use Doctrine\DBAL\Connection;
use Kununu\DataFixtures\Adapter\ConnectionFixtureInterface;
use Kununu\TestingBundle\Traits\ConnectionToolsTrait;

final class ConnectionFixture2 implements ConnectionFixtureInterface
{
    use ConnectionToolsTrait;

    public function load(Connection $connection): void
    {
        $this->executeQuery($connection, 'INSERT INTO `table_1` (`name`, `description`) VALUES (\'name\', \'description\');');
        $this->executeQuery($connection, 'INSERT INTO `table_2` (`name`, `description`) VALUES (\'name\', \'description\');');
    }
}
