<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Integration\Test;

use Doctrine\DBAL\Connection;
use Kununu\TestingBundle\Test\FixturesAwareTestCase;
use Kununu\TestingBundle\Test\FixturesContainerGetterTrait;
use Kununu\TestingBundle\Test\Options\DbOptions;
use Kununu\TestingBundle\Tests\App\Fixtures\Connection\ConnectionFixture1;
use Kununu\TestingBundle\Tests\App\Fixtures\Connection\ConnectionSqlFixture1;

final class FixturesAwareTestCaseConnectionsTest extends FixturesAwareTestCase
{
    use FixturesContainerGetterTrait;

    private Connection $defConnection;
    private Connection $monolithicConnection;

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

        self::assertEquals(4, (int) $this->defConnection->fetchOne('SELECT COUNT(*) FROM `table_1`'));
        self::assertEquals(4, (int) $this->defConnection->fetchOne('SELECT COUNT(*) FROM `table_2`'));
        self::assertEquals(1, (int) $this->defConnection->fetchOne('SELECT COUNT(*) FROM `table_to_exclude`'));

        self::assertEquals(4, (int) $this->monolithicConnection->fetchOne('SELECT COUNT(*) FROM `table_1`'));
        self::assertEquals(4, (int) $this->monolithicConnection->fetchOne('SELECT COUNT(*) FROM `table_2`'));
        self::assertEquals(
            1,
            (int) $this->monolithicConnection->fetchOne('SELECT COUNT(*) FROM `table_to_exclude`')
        );
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

        self::assertEquals(3, (int) $this->defConnection->fetchOne('SELECT COUNT(*) FROM `table_1`'));
        self::assertEquals(3, (int) $this->defConnection->fetchOne('SELECT COUNT(*) FROM `table_2`'));
        self::assertEquals(1, (int) $this->defConnection->fetchOne('SELECT COUNT(*) FROM `table_to_exclude`'));

        self::assertEquals(3, (int) $this->monolithicConnection->fetchOne('SELECT COUNT(*) FROM table_1'));
        self::assertEquals(3, (int) $this->monolithicConnection->fetchOne('SELECT COUNT(*) FROM table_2'));
        self::assertEquals(
            1,
            (int) $this->monolithicConnection->fetchOne('SELECT COUNT(*) FROM `table_to_exclude`')
        );
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

        self::assertEmpty($this->getDbFixtures('def', $options));
    }

    protected function setUp(): void
    {
        $this->defConnection = $this->getConnection('doctrine.dbal.def_connection');
        $this->monolithicConnection = $this->getConnection('doctrine.dbal.monolithic_connection');

        /** @var Connection $connection */
        foreach ([$this->defConnection, $this->monolithicConnection] as $connection) {
            $connection->executeStatement('TRUNCATE `table_1`');
            $connection->executeStatement('TRUNCATE `table_2`');
            $connection->executeStatement('TRUNCATE `table_3`');
            $connection->executeStatement('TRUNCATE `table_to_exclude`');
            $connection->executeStatement(
                'INSERT INTO `table_1` (`name`, `description`) VALUES (\'name\', \'description\');'
            );
            $connection->executeStatement(
                'INSERT INTO `table_2` (`name`, `description`) VALUES (\'name\', \'description\');'
            );
            $connection->executeStatement(
                'INSERT INTO `table_3` (`name`, `description`) VALUES (\'name\', \'description\');'
            );
            $connection->executeStatement(
                'INSERT INTO `table_to_exclude` (`name`, `description`) VALUES (\'name\', \'description\');'
            );
        }
    }
}
