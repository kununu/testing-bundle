<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Test;

use Doctrine\DBAL\Connection;
use Kununu\TestingBundle\Test\FixturesAwareTestCase;
use Kununu\TestingBundle\Tests\App\Fixtures\Connection\ConnectionFixture1;
use Kununu\TestingBundle\Tests\App\Fixtures\Connection\ConnectionSqlFixture1;
use Kununu\TestingBundle\Traits\ConnectionToolsTrait;

/**
 * @group legacy
 */
final class FixturesAwareTestCaseNonTransactionalConnectionsTest extends FixturesAwareTestCase
{
    use ConnectionToolsTrait;

    /** @var Connection */
    private $defConnection;

    /** @var Connection */
    private $monolithicConnection;

    public function testLoadDbFixturesWithAppend(): void
    {
        $this->registerInitializableFixtureForNonTransactionalDb(
            'def',
            ConnectionFixture1::class,
            'default_connection',
            true
        );
        $this->registerInitializableFixtureForNonTransactionalDb(
            'monolithic',
            ConnectionFixture1::class,
            'monolithic_connection',
            false
        );

        $this->loadDbNonTransactionalFixtures(
            'def',
            [ConnectionFixture1::class, ConnectionFixture1::class, ConnectionSqlFixture1::class],
            true
        );

        $this->loadDbNonTransactionalFixtures(
            'monolithic',
            [ConnectionFixture1::class, ConnectionFixture1::class, ConnectionSqlFixture1::class],
            true
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
        $this->loadDbNonTransactionalFixtures(
            'def',
            [ConnectionFixture1::class, ConnectionFixture1::class, ConnectionSqlFixture1::class],
            false
        );

        $this->loadDbNonTransactionalFixtures(
            'monolithic',
            [ConnectionFixture1::class, ConnectionFixture1::class, ConnectionSqlFixture1::class],
            false
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
        $this->loadDbNonTransactionalFixtures('def', [ConnectionFixture1::class, ConnectionFixture1::class, ConnectionSqlFixture1::class]);
        $this->clearDbNonTransactionalFixtures('def');
        $this->assertEmpty($this->getDbNonTransactionalFixtures('def'));
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
