<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Test;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use Elasticsearch\Client as ElasticSearch;
use Elasticsearch\Common\Exceptions\Missing404Exception;
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

    /** @var CacheItemPoolInterface */
    private $tagAwareCachePool;

    /** @var CacheItemPoolInterface */
    private $tagAwarePoolCachePool;

    /** @var CacheItemPoolInterface */
    private $chainCachePool;

    /** @var ElasticSearch */
    private $elasticSearch;

    public function testLoadElasticSearchFixturesWithoutAppend(): void
    {
        $this->elasticSearch->index(
            [
                'index' => 'my_index',
                'id'    => 'document_to_purge',
                'body'  => ['field' => 'value1'],
            ]
        );

        $this->elasticSearch->get(['index' => 'my_index', 'id' => 'document_to_purge']);

        $this->registerInitializableFixtureForElasticSearch(
            'my_index_alias',
            ElasticSearchFixture1::class,
            1,
            ['a' => 'name']
        );
        $this->loadElasticSearchFixtures(
            'my_index_alias',
            [ElasticSearchFixture1::class, ElasticSearchFixture2::class]
        );

        $purgedDocument = null;

        try {
            $purgedDocument = $this->elasticSearch->get(['index' => 'my_index', 'id' => 'document_to_purge']);
        } catch (Missing404Exception $missing404Exception) {
        }

        if (!empty($purgedDocument)) {
            $this->fail('Document should not exist!');
        }

        $document1 = $this->elasticSearch->get(['index' => 'my_index', 'id' => 'my_id_1']);
        $document2 = $this->elasticSearch->get(['index' => 'my_index', 'id' => 'my_id_2']);

        $this->assertEquals('value_1', $document1['_source']['field']);
        $this->assertEquals('value_2', $document2['_source']['field']);
    }

    public function testLoadElasticSearchFixturesWithAppend(): void
    {
        $this->elasticSearch->index(
            [
                'index' => 'my_index',
                'id'    => 'document_to_not_purge',
                'body'  => ['field' => 'value1'],
            ]
        );

        $this->elasticSearch->get(['index' => 'my_index', 'id' => 'document_to_not_purge']);

        $this->loadElasticSearchFixtures(
            'my_index_alias',
            [ElasticSearchFixture1::class, ElasticSearchFixture2::class],
            true
        );

        $this->elasticSearch->get(['index' => 'my_index', 'id' => 'document_to_not_purge']);

        $document1 = $this->elasticSearch->get(['index' => 'my_index', 'id' => 'my_id_1']);
        $document2 = $this->elasticSearch->get(['index' => 'my_index', 'id' => 'my_id_2']);

        $this->assertEquals('value_1', $document1['_source']['field']);
        $this->assertEquals('value_2', $document2['_source']['field']);
    }

    public function testLoadCachePoolFixturesWithoutAppend(): void
    {
        $cachePool1ItemToPurge1 = $this->cachePool->getItem('cache_pool_1_key_to_purge_1');
        $cachePool1ItemToPurge1->set('value_to_purge_1');
        $this->cachePool->save($cachePool1ItemToPurge1);

        $this->registerInitializableFixtureForCachePool(
            'app.cache.first',
            CachePoolFixture1::class,
            $this->monolithicConnection
        );
        $this->loadCachePoolFixtures(
            'app.cache.first',
            [CachePoolFixture1::class, CachePoolFixture2::class]
        );

        $cachePool1ItemAfterPurge1 = $this->cachePool->getItem('cache_pool_1_key_to_purge_1');
        $cachePool1Item1 = $this->cachePool->getItem('key_1');
        $cachePool1Item2 = $this->cachePool->getItem('key_2');
        $cachePool1Item3 = $this->cachePool->getItem('key_3');

        $this->assertNull($cachePool1ItemAfterPurge1->get());
        $this->assertEquals('value_1', $cachePool1Item1->get());
        $this->assertEquals('value_2', $cachePool1Item2->get());
        $this->assertEquals('value_3', $cachePool1Item3->get());
    }

    public function testLoadCachePoolFixturesWithAppend(): void
    {
        $cachePool1ItemToNotPurge1 = $this->cachePool->getItem('cache_pool_1_key_to_not_purge_1');
        $cachePool1ItemToNotPurge1->set('value_to_not_purge_1');
        $this->cachePool->save($cachePool1ItemToNotPurge1);

        $this->loadCachePoolFixtures(
            'app.cache.first',
            [CachePoolFixture1::class, CachePoolFixture2::class],
            true
        );

        $cachePool1ItemAfterToNotPurge1 = $this->cachePool->getItem('cache_pool_1_key_to_not_purge_1');
        $cachePool1Item1 = $this->cachePool->getItem('key_1');
        $cachePool1Item2 = $this->cachePool->getItem('key_2');
        $cachePool1Item3 = $this->cachePool->getItem('key_3');

        $this->assertEquals('value_to_not_purge_1', $cachePool1ItemAfterToNotPurge1->get());
        $this->assertEquals('value_1', $cachePool1Item1->get());
        $this->assertEquals('value_2', $cachePool1Item2->get());
        $this->assertEquals('value_3', $cachePool1Item3->get());
    }

    public function testLoadTagAwareCachePoolFixturesWithoutAppend(): void
    {
        $cachePool1ItemToPurge1 = $this->tagAwareCachePool->getItem('cache_pool_1_key_to_purge_1');

        $cachePool1ItemToPurge1->set('value_to_purge_1');
        $this->tagAwareCachePool->save($cachePool1ItemToPurge1);

        $this->registerInitializableFixtureForCachePool(
            'app.cache.third',
            CachePoolFixture1::class,
            $this->monolithicConnection
        );

        $this->loadCachePoolFixtures(
            'app.cache.third',
            [CachePoolFixture1::class, CachePoolFixture2::class]
        );

        $cachePool1ItemAfterPurge1 = $this->tagAwareCachePool->getItem('cache_pool_1_key_to_purge_1');
        $cachePool1Item1 = $this->tagAwareCachePool->getItem('key_1');
        $cachePool1Item2 = $this->tagAwareCachePool->getItem('key_2');
        $cachePool1Item3 = $this->tagAwareCachePool->getItem('key_3');

        $this->assertNull($cachePool1ItemAfterPurge1->get());
        $this->assertEquals('value_1', $cachePool1Item1->get());
        $this->assertEquals('value_2', $cachePool1Item2->get());
        $this->assertEquals('value_3', $cachePool1Item3->get());
    }

    public function testLoadTagAwareCachePoolFixturesWithAppend(): void
    {
        $cachePool1ItemToNotPurge1 = $this->tagAwareCachePool->getItem('cache_pool_1_key_to_not_purge_1');
        $cachePool1ItemToNotPurge1->set('value_to_not_purge_1');
        $this->tagAwareCachePool->save($cachePool1ItemToNotPurge1);

        $this->loadCachePoolFixtures(
            'app.cache.third',
            [CachePoolFixture1::class, CachePoolFixture2::class],
            true
        );

        $cachePool1ItemAfterToNotPurge1 = $this->tagAwareCachePool->getItem('cache_pool_1_key_to_not_purge_1');
        $cachePool1Item1 = $this->tagAwareCachePool->getItem('key_1');
        $cachePool1Item2 = $this->tagAwareCachePool->getItem('key_2');
        $cachePool1Item3 = $this->tagAwareCachePool->getItem('key_3');

        $this->assertEquals('value_to_not_purge_1', $cachePool1ItemAfterToNotPurge1->get());
        $this->assertEquals('value_1', $cachePool1Item1->get());
        $this->assertEquals('value_2', $cachePool1Item2->get());
        $this->assertEquals('value_3', $cachePool1Item3->get());
    }

    public function testLoadTagAwarePoolCachePoolFixturesWithoutAppend(): void
    {
        $cachePool1ItemToPurge1 = $this->tagAwarePoolCachePool->getItem('cache_pool_1_key_to_purge_1');

        $cachePool1ItemToPurge1->set('value_to_purge_1');
        $this->tagAwarePoolCachePool->save($cachePool1ItemToPurge1);

        $this->registerInitializableFixtureForCachePool(
            'app.cache.fourth',
            CachePoolFixture1::class,
            $this->monolithicConnection
        );

        $this->loadCachePoolFixtures(
            'app.cache.fourth',
            [CachePoolFixture1::class, CachePoolFixture2::class]
        );

        $cachePool1ItemAfterPurge1 = $this->tagAwarePoolCachePool->getItem('cache_pool_1_key_to_purge_1');
        $cachePool1Item1 = $this->tagAwarePoolCachePool->getItem('key_1');
        $cachePool1Item2 = $this->tagAwarePoolCachePool->getItem('key_2');
        $cachePool1Item3 = $this->tagAwarePoolCachePool->getItem('key_3');

        $this->assertNull($cachePool1ItemAfterPurge1->get());
        $this->assertEquals('value_1', $cachePool1Item1->get());
        $this->assertEquals('value_2', $cachePool1Item2->get());
        $this->assertEquals('value_3', $cachePool1Item3->get());
    }

    public function testLoadTagAwarePoolCachePoolFixturesWithAppend(): void
    {
        $cachePool1ItemToNotPurge1 = $this->tagAwarePoolCachePool->getItem('cache_pool_1_key_to_not_purge_1');
        $cachePool1ItemToNotPurge1->set('value_to_not_purge_1');
        $this->tagAwarePoolCachePool->save($cachePool1ItemToNotPurge1);

        $this->loadCachePoolFixtures(
            'app.cache.fourth',
            [CachePoolFixture1::class, CachePoolFixture2::class],
            true
        );

        $cachePool1ItemAfterToNotPurge1 = $this->tagAwarePoolCachePool->getItem('cache_pool_1_key_to_not_purge_1');
        $cachePool1Item1 = $this->tagAwarePoolCachePool->getItem('key_1');
        $cachePool1Item2 = $this->tagAwarePoolCachePool->getItem('key_2');
        $cachePool1Item3 = $this->tagAwarePoolCachePool->getItem('key_3');

        $this->assertEquals('value_to_not_purge_1', $cachePool1ItemAfterToNotPurge1->get());
        $this->assertEquals('value_1', $cachePool1Item1->get());
        $this->assertEquals('value_2', $cachePool1Item2->get());
        $this->assertEquals('value_3', $cachePool1Item3->get());
    }

    public function testLoadChainCachePoolFixturesWithoutAppend(): void
    {
        $cachePool1ItemToPurge1 = $this->chainCachePool->getItem('cache_pool_1_key_to_purge_1');

        $cachePool1ItemToPurge1->set('value_to_purge_1');
        $this->chainCachePool->save($cachePool1ItemToPurge1);

        $this->registerInitializableFixtureForCachePool(
            'app.cache.fifth',
            CachePoolFixture1::class,
            $this->monolithicConnection
        );

        $this->loadCachePoolFixtures(
            'app.cache.fifth',
            [CachePoolFixture1::class, CachePoolFixture2::class]
        );

        $cachePool1ItemAfterPurge1 = $this->chainCachePool->getItem('cache_pool_1_key_to_purge_1');
        $cachePool1Item1 = $this->chainCachePool->getItem('key_1');
        $cachePool1Item2 = $this->chainCachePool->getItem('key_2');
        $cachePool1Item3 = $this->chainCachePool->getItem('key_3');

        $this->assertNull($cachePool1ItemAfterPurge1->get());
        $this->assertEquals('value_1', $cachePool1Item1->get());
        $this->assertEquals('value_2', $cachePool1Item2->get());
        $this->assertEquals('value_3', $cachePool1Item3->get());
    }

    public function testLoadChainPoolCachePoolFixturesWithAppend(): void
    {
        $cachePool1ItemToNotPurge1 = $this->chainCachePool->getItem('cache_pool_1_key_to_not_purge_1');
        $cachePool1ItemToNotPurge1->set('value_to_not_purge_1');
        $this->chainCachePool->save($cachePool1ItemToNotPurge1);

        $this->loadCachePoolFixtures(
            'app.cache.fifth',
            [CachePoolFixture1::class, CachePoolFixture2::class],
            true
        );

        $cachePool1ItemAfterToNotPurge1 = $this->chainCachePool->getItem('cache_pool_1_key_to_not_purge_1');
        $cachePool1Item1 = $this->chainCachePool->getItem('key_1');
        $cachePool1Item2 = $this->chainCachePool->getItem('key_2');
        $cachePool1Item3 = $this->chainCachePool->getItem('key_3');

        $this->assertEquals('value_to_not_purge_1', $cachePool1ItemAfterToNotPurge1->get());
        $this->assertEquals('value_1', $cachePool1Item1->get());
        $this->assertEquals('value_2', $cachePool1Item2->get());
        $this->assertEquals('value_3', $cachePool1Item3->get());
    }

    public function testLoadDbFixturesWithAppend(): void
    {
        $this->registerInitializableFixtureForDb(
            'default',
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
            'default',
            [ConnectionFixture1::class, ConnectionFixture1::class, ConnectionSqlFixture1::class],
            true
        );

        $this->loadDbFixtures(
            'monolithic',
            [ConnectionFixture1::class, ConnectionFixture1::class, ConnectionSqlFixture1::class],
            true
        );

        $this->assertEquals(4, (int) $this->defaultConnection->fetchOne('SELECT COUNT(*) FROM table_1'));
        $this->assertEquals(4, (int) $this->defaultConnection->fetchOne('SELECT COUNT(*) FROM table_2'));
        $this->assertEquals(1, (int) $this->defaultConnection->fetchOne('SELECT COUNT(*) FROM table_to_exclude'));

        $this->assertEquals(4, (int) $this->monolithicConnection->fetchOne('SELECT COUNT(*) FROM table_1'));
        $this->assertEquals(4, (int) $this->monolithicConnection->fetchOne('SELECT COUNT(*) FROM table_2'));
        $this->assertEquals(1, (int) $this->monolithicConnection->fetchOne('SELECT COUNT(*) FROM table_to_exclude'));
    }

    public function testLoadDbFixturesWithoutAppend(): void
    {
        $this->loadDbFixtures(
            'default',
            [ConnectionFixture1::class, ConnectionFixture1::class, ConnectionSqlFixture1::class],
            false
        );

        $this->loadDbFixtures(
            'monolithic',
            [ConnectionFixture1::class, ConnectionFixture1::class, ConnectionSqlFixture1::class],
            false
        );

        $this->assertEquals(3, (int) $this->defaultConnection->fetchOne('SELECT COUNT(*) FROM table_1'));
        $this->assertEquals(3, (int) $this->defaultConnection->fetchOne('SELECT COUNT(*) FROM table_2'));
        $this->assertEquals(1, (int) $this->defaultConnection->fetchOne('SELECT COUNT(*) FROM table_to_exclude'));

        $this->assertEquals(3, (int) $this->monolithicConnection->fetchOne('SELECT COUNT(*) FROM table_1'));
        $this->assertEquals(3, (int) $this->monolithicConnection->fetchOne('SELECT COUNT(*) FROM table_2'));
        $this->assertEquals(1, (int) $this->monolithicConnection->fetchOne('SELECT COUNT(*) FROM table_to_exclude'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->cachePool = $this->getContainer()->get('app.cache.first');
        $this->tagAwareCachePool = $this->getContainer()->get('app.cache.third');
        $this->tagAwarePoolCachePool = $this->getContainer()->get('app.cache.fourth');
        $this->chainCachePool = $this->getContainer()->get('app.cache.fifth');

        $this->defaultConnection = $this->getContainer()->get('doctrine.dbal.default_connection');
        $this->monolithicConnection = $this->getContainer()->get('doctrine.dbal.monolithic_connection');
        $this->elasticSearch = $this->getContainer()->get('Kununu\TestingBundle\Tests\App\ElasticSearch');

        $this->recreateConnectionDatabase(
            $this->defaultConnection,
            $this->getContainer()->getParameter('doctrine_default_connection_path')
        );
        $this->recreateConnectionDatabase(
            $this->monolithicConnection,
            $this->getContainer()->getParameter('doctrine_monolithic_connection_path')
        );
    }

    private function recreateConnectionDatabase(Connection $connection, string $databaseName): void
    {
        $table1 = new Table('table_1', [
            new Column('name', Type::getType('string')),
            new Column('description', Type::getType('string')),
        ]);

        $table2 = new Table('table_2', [
            new Column('name', Type::getType('string')),
            new Column('description', Type::getType('string')),
        ]);

        $table3 = (new Table('table_3', [
            new Column('name', Type::getType('string')),
            new Column('description', Type::getType('string')),
        ]))->setPrimaryKey(['name']);

        $tableToExclude = new Table('table_to_exclude', [
            new Column('name', Type::getType('string')),
            new Column('description', Type::getType('string')),
        ]);

        $schemaManager = $connection->createSchemaManager();

        // Workaround with Sqlite since dropAndCreateDatabase is not working.
        // @todo Investigate further and open PR in doctrine repository to fix it
        $schemaManager->dropDatabase($databaseName);
        $schemaManager->createDatabase($connection->getDatabase());
        $schemaManager->createTable($table1);
        $schemaManager->createTable($table2);
        $schemaManager->createTable($table3);
        $schemaManager->createTable($tableToExclude);

        $connection->executeStatement('INSERT INTO `table_1` (`name`, `description`) VALUES (\'name\', \'description\');');
        $connection->executeStatement('INSERT INTO `table_2` (`name`, `description`) VALUES (\'name\', \'description\');');
        $connection->executeStatement('INSERT INTO `table_3` (`name`, `description`) VALUES (\'name\', \'description\');');
        $connection->executeStatement('INSERT INTO `table_to_exclude` (`name`, `description`) VALUES (\'name\', \'description\');');
    }
}
