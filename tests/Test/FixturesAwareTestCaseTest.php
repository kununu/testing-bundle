<?php declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Test;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use Elasticsearch\Client as ElasticSearch;
use Kununu\TestingBundle\Test\FixturesAwareTestCase;
use Kununu\TestingBundle\Tests\App\Fixtures\CachePool\CachePoolFixture1;
use Kununu\TestingBundle\Tests\App\Fixtures\CachePool\CachePoolFixture2;
use Kununu\TestingBundle\Tests\App\Fixtures\Connection\ConnectionFixture1;
use Kununu\TestingBundle\Tests\App\Fixtures\Connection\ConnectionSqlFixture1;
use Kununu\TestingBundle\Tests\App\Fixtures\ElasticSearch\ElasticSearchFixture1;
use Kununu\TestingBundle\Tests\App\Fixtures\ElasticSearch\ElasticSearchFixture2;
use Psr\Cache\CacheItemPoolInterface;

/**
 * @group integration
 */
final class FixturesAwareTestCaseTest extends FixturesAwareTestCase
{
    /** @var Connection */
    private $defaultConnection;

    /** @var Connection */
    private $monolithicConnection;

    /** @var CacheItemPoolInterface */
    private $cachePool;

    /** @var ElasticSearch */
    private $elasticSearch;

    public function testLoadElasticSearchFixturesWithoutAppend()
    {
        $this->elasticSearch->index(
            [
                'index' => 'my_index',
                'id'    => 'document_to_purge',
                'body'  => ['field' => 'value1']
            ]
        );

        $this->loadElasticSearchFixtures(
            'my_index_alias',
            [ElasticSearchFixture1::class, ElasticSearchFixture2::class]
        );

        $purgedDocument = $this->elasticSearch->get(['index' => 'my_index', 'id' => 'document_to_purge']);

        $document1 = $this->elasticSearch->get(['index' => 'my_index', 'id' => 'my_id_1']);
        $document2 = $this->elasticSearch->get(['index' => 'my_index', 'id' => 'my_id_2']);

        $this->assertEquals('value_1', $document1['_source']['field']);
        $this->assertEquals('value_2', $document2['_source']['field']);
    }

//    public function testLoadCachePoolFixturesWithoutAppend()
//    {
//        $cachePool1ItemToPurge1 = $this->cachePool->getItem('cache_pool_1_key_to_purge_1');
//        $cachePool1ItemToPurge1->set('value_to_purge_1');
//        $this->cachePool->save($cachePool1ItemToPurge1);
//
//        $this->loadCachePoolFixtures(
//            'app.cache.first',
//            [CachePoolFixture1::class,CachePoolFixture2::class]
//        );
//
//        $cachePool1ItemAfterPurge1 = $this->cachePool->getItem('cache_pool_1_key_to_purge_1');
//        $cachePool1Item1 = $this->cachePool->getItem('key_1');
//        $cachePool1Item2 = $this->cachePool->getItem('key_2');
//        $cachePool1Item3 = $this->cachePool->getItem('key_3');
//
//        $this->assertNull($cachePool1ItemAfterPurge1->get());
//        $this->assertEquals('value_1', $cachePool1Item1->get());
//        $this->assertEquals('value_2', $cachePool1Item2->get());
//        $this->assertEquals('value_3', $cachePool1Item3->get());
//    }
//
//    public function testLoadCachePoolFixturesWithAppend()
//    {
//        $cachePool1ItemToNotPurge1 = $this->cachePool->getItem('cache_pool_1_key_to_not_purge_1');
//        $cachePool1ItemToNotPurge1->set('value_to_not_purge_1');
//        $this->cachePool->save($cachePool1ItemToNotPurge1);
//
//        $this->loadCachePoolFixtures(
//            'app.cache.first',
//            [CachePoolFixture1::class,CachePoolFixture2::class],
//            true
//        );
//
//        $cachePool1ItemAfterToNotPurge1 = $this->cachePool->getItem('cache_pool_1_key_to_not_purge_1');
//        $cachePool1Item1 = $this->cachePool->getItem('key_1');
//        $cachePool1Item2 = $this->cachePool->getItem('key_2');
//        $cachePool1Item3 = $this->cachePool->getItem('key_3');
//
//        $this->assertEquals('value_to_not_purge_1', $cachePool1ItemAfterToNotPurge1->get());
//        $this->assertEquals('value_1', $cachePool1Item1->get());
//        $this->assertEquals('value_2', $cachePool1Item2->get());
//        $this->assertEquals('value_3', $cachePool1Item3->get());
//    }
//
//    public function testLoadDbFixturesWithAppend()
//    {
//        $this->loadDbFixtures(
//            'default',
//            [ConnectionFixture1::class, ConnectionFixture1::class, ConnectionSqlFixture1::class],
//            true
//        );
//
//        $this->loadDbFixtures(
//            'monolithic',
//            [ConnectionFixture1::class, ConnectionFixture1::class, ConnectionSqlFixture1::class],
//            true
//        );
//
//        $this->assertEquals(4, (int)$this->defaultConnection->fetchColumn('SELECT COUNT(*) FROM table_1'));
//        $this->assertEquals(4, (int)$this->defaultConnection->fetchColumn('SELECT COUNT(*) FROM table_2'));
//        $this->assertEquals(1, (int)$this->defaultConnection->fetchColumn('SELECT COUNT(*) FROM table_to_exclude'));
//
//        $this->assertEquals(4, (int)$this->monolithicConnection->fetchColumn('SELECT COUNT(*) FROM table_1'));
//        $this->assertEquals(4, (int)$this->monolithicConnection->fetchColumn('SELECT COUNT(*) FROM table_2'));
//        $this->assertEquals(1, (int)$this->monolithicConnection->fetchColumn('SELECT COUNT(*) FROM table_to_exclude'));
//    }
//
//    public function testLoadDbFixturesWithoutAppend()
//    {
//        $this->loadDbFixtures(
//            'default',
//            [ConnectionFixture1::class, ConnectionFixture1::class, ConnectionSqlFixture1::class],
//            false
//        );
//
//        $this->loadDbFixtures(
//            'monolithic',
//            [ConnectionFixture1::class, ConnectionFixture1::class, ConnectionSqlFixture1::class],
//            false
//        );
//
//        $this->assertEquals(3, (int)$this->defaultConnection->fetchColumn('SELECT COUNT(*) FROM table_1'));
//        $this->assertEquals(3, (int)$this->defaultConnection->fetchColumn('SELECT COUNT(*) FROM table_2'));
//        $this->assertEquals(1, (int)$this->defaultConnection->fetchColumn('SELECT COUNT(*) FROM table_to_exclude'));
//
//        $this->assertEquals(3, (int)$this->monolithicConnection->fetchColumn('SELECT COUNT(*) FROM table_1'));
//        $this->assertEquals(3, (int)$this->monolithicConnection->fetchColumn('SELECT COUNT(*) FROM table_2'));
//        $this->assertEquals(1, (int)$this->monolithicConnection->fetchColumn('SELECT COUNT(*) FROM table_to_exclude'));
//    }

    protected function setUp(): void
    {
        parent::setUp();

//        $this->cachePool = $this->getContainer()->get('app.cache.first');
//        $this->defaultConnection = $this->getContainer()->get('doctrine.dbal.default_connection');
//        $this->monolithicConnection = $this->getContainer()->get('doctrine.dbal.monolithic_connection');
        $this->elasticSearch = $this->getContainer()->get('Kununu\TestingBundle\Tests\App\ElasticSearch');
//
//        $this->recreateConnectionDatabase($this->defaultConnection);
//        $this->recreateConnectionDatabase($this->monolithicConnection);
    }

    private function recreateConnectionDatabase(Connection $connection) : void
    {
        $table1 = new Table('table_1', [
            new Column('name', Type::getType('string')),
            new Column('description', Type::getType('string'))
        ]);

        $table2 = new Table('table_2', [
            new Column('name', Type::getType('string')),
            new Column('description', Type::getType('string'))
        ]);

        $tableToExclude = new Table('table_to_exclude', [
            new Column('name', Type::getType('string')),
            new Column('description', Type::getType('string'))
        ]);

        $schemaManager = $connection->getSchemaManager();
        $schemaManager->dropAndCreateDatabase($connection->getDatabase());
        $schemaManager->createTable($table1);
        $schemaManager->createTable($table2);
        $schemaManager->createTable($tableToExclude);

        $connection->exec('INSERT INTO `table_1` (`name`, `description`) VALUES (\'name\', \'description\');');
        $connection->exec('INSERT INTO `table_2` (`name`, `description`) VALUES (\'name\', \'description\');');
        $connection->exec('INSERT INTO `table_to_exclude` (`name`, `description`) VALUES (\'name\', \'description\');');
    }
}
