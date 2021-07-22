<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\App\Fixtures\Connection;

use Doctrine\DBAL\Connection;
use Kununu\DataFixtures\Adapter\ConnectionFixtureInterface;
use Kununu\TestingBundle\Traits\ConnectionToolsTrait;

final class ConnectionFixture6 implements ConnectionFixtureInterface
{
    use ConnectionToolsTrait;

    public function load(Connection $connection): void
    {
        $this->executeQuery($connection, 'INSERT INTO `table_3` (`name`, `description`) VALUES (\'my_name\', \'description6\');');
    }
}
