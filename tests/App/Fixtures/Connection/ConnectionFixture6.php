<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\App\Fixtures\Connection;

use Doctrine\DBAL\Connection;
use Kununu\DataFixtures\Adapter\ConnectionFixtureInterface;
use Kununu\TestingBundle\Tests\ConnectionHelperTrait;

final class ConnectionFixture6 implements ConnectionFixtureInterface
{
    use ConnectionHelperTrait;

    public function load(Connection $connection): void
    {
        $this->executeQuery($connection, 'INSERT INTO `table_3` (`name`, `description`) VALUES (\'my_name\', \'description6\');');
    }
}
