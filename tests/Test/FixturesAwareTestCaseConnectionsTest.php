<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Test;

use Doctrine\DBAL\Connection;
use Kununu\TestingBundle\Test\FixturesAwareTestCase;
use Kununu\TestingBundle\Test\Options\DbOptions;
use Kununu\TestingBundle\Tests\App\Fixtures\Connection\ConnectionFixture1;
use Kununu\TestingBundle\Tests\App\Fixtures\Connection\ConnectionSqlFixture1;
use Kununu\TestingBundle\Traits\ConnectionToolsTrait;

/**
 * @group legacy
 */
final class FixturesAwareTestCaseConnectionsTest extends FixturesAwareTestCase
{
    use ConnectionToolsTrait;

    /** @var Connection */
    private $defConnection;

    /** @var Connection */
    private $monolithicConnection;

    public function testLoadDbFixturesWithAppend(): void
    {
        $this->registerInitializableFixtureForDb(
            'def',
            ConnectionFixture1::class,
            'default_connection',
            true
        );
        $this->registerInitializableFixtureForDb(
            'monolithic',
            ConnectionFixture1::class,
            'monolithic_connection',
            false
        );

        $this->loadDbFixtures(
            'def',
            $options = DbOptions::create()->withAppend(),
            ConnectionFixture1::class,
            ConnectionFixture1::class,
            ConnectionSqlFixture1::class
        );

        $this->loadDbFixtures(
            'monolithic',
            $options,
            ConnectionFixture1::class,
            ConnectionFixture1::class,
            ConnectionSqlFixture1::class
        );

        $this->assertEquals(4, (int) $this->fetchOne($this->defConnection, 'SELECT COUNT(*) FROM table_1'));
        $this->assertEquals(4, (int) $this->fetchOne($this->defConnection, 'SELECT COUNT(*) FROM table_2'));
        $this->assertEquals(1, (int) $this->fetchOne($this->defConnection, 'SELECT COUNT(*) FROM table_to_exclude'));

        $this->assertEquals(4, (int) $this->fetchOne($this->monolithicConnection, 'SELECT COUNT(*) FROM table_1'));
        $this->assertEquals(4, (int) $this->fetchOne($this->monolithicConnection, 'SELECT COUNT(*) FROM table_2'));
        $this->assertEquals(1, (int) $this->fetchOne($this->monolithicConnection, 'SELECT COUNT(*) FROM table_to_exclude'));
    }

    public function testLoadDbFixturesWithoutAppend(): void
    {
        $this->loadDbFixtures(
            'def',
            $options = DbOptions::create(),
            ConnectionFixture1::class,
            ConnectionFixture1::class,
            ConnectionSqlFixture1::class
        );

        $this->loadDbFixtures(
            'monolithic',
            $options,
            ConnectionFixture1::class,
            ConnectionFixture1::class,
            ConnectionSqlFixture1::class
        );

        $this->assertEquals(3, (int) $this->fetchOne($this->defConnection, 'SELECT COUNT(*) FROM table_1'));
        $this->assertEquals(3, (int) $this->fetchOne($this->defConnection, 'SELECT COUNT(*) FROM table_2'));
        $this->assertEquals(1, (int) $this->fetchOne($this->defConnection, 'SELECT COUNT(*) FROM table_to_exclude'));

        $this->assertEquals(3, (int) $this->fetchOne($this->monolithicConnection, 'SELECT COUNT(*) FROM table_1'));
        $this->assertEquals(3, (int) $this->fetchOne($this->monolithicConnection, 'SELECT COUNT(*) FROM table_2'));
        $this->assertEquals(1, (int) $this->fetchOne($this->monolithicConnection, 'SELECT COUNT(*) FROM table_to_exclude'));
    }

    public function testClearFixtures(): void
    {
        $this->loadDbFixtures(
            'def',
            $options = DbOptions::create(),
            ConnectionFixture1::class,
            ConnectionFixture1::class,
            ConnectionSqlFixture1::class
        );
        $this->clearDbFixtures('def', $options);

        $this->assertEmpty($this->getDbFixtures('def', $options));
    }

    protected function setUp(): void
    {
        $this->defConnection = $this->getFixturesContainer()->get('doctrine.dbal.def_connection');
        $this->monolithicConnection = $this->getFixturesContainer()->get('doctrine.dbal.monolithic_connection');

        /** @var Connection $connection */
        foreach ([$this->defConnection, $this->monolithicConnection] as $connection) {
            $this->executeQuery($connection, 'TRUNCATE `table_1`');
            $this->executeQuery($connection, 'TRUNCATE `table_2`');
            $this->executeQuery($connection, 'TRUNCATE `table_3`');
            $this->executeQuery($connection, 'TRUNCATE `table_to_exclude`');
            $this->executeQuery($connection, 'INSERT INTO `table_1` (`name`, `description`) VALUES (\'name\', \'description\');');
            $this->executeQuery($connection, 'INSERT INTO `table_2` (`name`, `description`) VALUES (\'name\', \'description\');');
            $this->executeQuery($connection, 'INSERT INTO `table_3` (`name`, `description`) VALUES (\'name\', \'description\');');
            $this->executeQuery($connection, 'INSERT INTO `table_to_exclude` (`name`, `description`) VALUES (\'name\', \'description\');');
        }
    }
}
