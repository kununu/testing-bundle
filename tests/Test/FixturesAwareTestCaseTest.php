<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Test;

use Doctrine\DBAL\Connection;
use Elasticsearch\Client as ElasticSearch;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Kununu\TestingBundle\Test\FixturesAwareTestCase;
use Kununu\TestingBundle\Tests\App\Fixtures\CachePool\CachePoolFixture1;
use Kununu\TestingBundle\Tests\App\Fixtures\CachePool\CachePoolFixture2;
use Kununu\TestingBundle\Tests\App\Fixtures\Connection\ConnectionFixture1;
use Kununu\TestingBundle\Tests\App\Fixtures\Connection\ConnectionSqlFixture1;
use Kununu\TestingBundle\Tests\App\Fixtures\ElasticSearch\ElasticSearchFixture1;
use Kununu\TestingBundle\Tests\App\Fixtures\ElasticSearch\ElasticSearchFixture2;
use Kununu\TestingBundle\Tests\App\Fixtures\HttpClient\HttpClientFixture1;
use Kununu\TestingBundle\Tests\App\Fixtures\HttpClient\HttpClientFixture2;
use Kununu\TestingBundle\Traits\ConnectionToolsTrait;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @group legacy
 */
final class FixturesAwareTestCaseTest extends FixturesAwareTestCase
{
    use ConnectionToolsTrait;

    /** @var Connection */
    private $defConnection;

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

    /** @var HttpClientInterface */
    private $httpClient;

    public function testLoadElasticSearchFixturesWithoutAppend(): void
    {
        $this->elasticSearch->index(
            [
                'index' => 'my_index',
                'id'    => 'document_to_purge',
                'body'  => ['field' => 'value1'],
            ]
        );

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
            [ConnectionFixture1::class, ConnectionFixture1::class, ConnectionSqlFixture1::class],
            true
        );

        $this->loadDbFixtures(
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
        $this->loadDbFixtures(
            'def',
            [ConnectionFixture1::class, ConnectionFixture1::class, ConnectionSqlFixture1::class],
            false
        );

        $this->loadDbFixtures(
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

    public function testLoadHttpClientFixturesWithAppend(): void
    {
        $this->registerInitializableFixtureForHttpClient('http_client', HttpClientFixture1::class);

        $this->loadHttpClientFixtures('http_client', [HttpClientFixture1::class], true);
        $this->loadHttpClientFixtures('http_client', [HttpClientFixture2::class], true);

        $response = $this->httpClient->request(Request::METHOD_GET, 'https://my.server/b7dd0cc2-381d-4e92-bc9b-b78245142e0a/data');
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $response = $this->httpClient->request(Request::METHOD_GET, 'https://my.server/f2895c23-28cb-4020-b038-717cca64bf2d/data');
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            <<<'JSON'
{
    "id": 1000,
    "name": {
        "first": "The",
        "surname": "Name"
    },
    "age": 39,
    "newsletter": true
}
JSON
            ,
            $response->getContent()
        );
    }

    public function testLoadHttpClientFixturesWithoutAppend(): void
    {
        $this->loadHttpClientFixtures('http_client', [HttpClientFixture1::class]);
        $this->loadHttpClientFixtures('http_client', [HttpClientFixture2::class]);

        $response = $this->httpClient->request(Request::METHOD_GET, 'https://my.server/b7dd0cc2-381d-4e92-bc9b-b78245142e0a/data');
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());

        $response = $this->httpClient->request(Request::METHOD_GET, 'https://my.server/f2895c23-28cb-4020-b038-717cca64bf2d/data');
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            <<<'JSON'
{
    "id": 1000,
    "name": {
        "first": "The",
        "surname": "Name"
    },
    "age": 39,
    "newsletter": true
}
JSON
            ,
            $response->getContent()
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->cachePool = $this->getFixturesContainer()->get('app.cache.first');
        $this->tagAwareCachePool = $this->getFixturesContainer()->get('app.cache.third');
        $this->tagAwarePoolCachePool = $this->getFixturesContainer()->get('app.cache.fourth');
        $this->chainCachePool = $this->getFixturesContainer()->get('app.cache.fifth');

        $this->defConnection = $this->getFixturesContainer()->get('doctrine.dbal.def_connection');
        $this->monolithicConnection = $this->getFixturesContainer()->get('doctrine.dbal.monolithic_connection');
        $this->elasticSearch = $this->getFixturesContainer()->get('Kununu\TestingBundle\Tests\App\ElasticSearch');
        $this->httpClient = $this->getFixturesContainer()->get('http_client');

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
