<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Integration\Test;

use Kununu\TestingBundle\Test\FixturesAwareTestCase;
use Kununu\TestingBundle\Test\FixturesContainerGetterTrait;
use Kununu\TestingBundle\Test\Options\Options;
use Kununu\TestingBundle\Tests\App\Fixtures\CachePool\CachePoolFixture1;
use Kununu\TestingBundle\Tests\App\Fixtures\CachePool\CachePoolFixture2;
use Psr\Cache\CacheItemPoolInterface;

final class FixturesAwareTestCaseCachePoolTest extends FixturesAwareTestCase
{
    use FixturesContainerGetterTrait;

    private const string EXTRA_DATA_FOR_INIT = 'some extra data for init';

    private CacheItemPoolInterface $cachePool;
    private CacheItemPoolInterface $tagAwareCachePool;
    private CacheItemPoolInterface $tagAwarePoolCachePool;
    private CacheItemPoolInterface $chainCachePool;

    public function testLoadCachePoolFixturesWithoutAppend(): void
    {
        $cachePool1ItemToPurge1 = $this->cachePool->getItem('cache_pool_1_key_to_purge_1');
        $cachePool1ItemToPurge1->set('value_to_purge_1');
        $this->cachePool->save($cachePool1ItemToPurge1);

        $this->registerInitializableFixtureForCachePool(
            'app.cache.first',
            CachePoolFixture1::class,
            self::EXTRA_DATA_FOR_INIT
        );
        $this->loadCachePoolFixtures(
            'app.cache.first',
            Options::create(),
            CachePoolFixture1::class,
            CachePoolFixture2::class
        );

        $cachePool1ItemAfterPurge1 = $this->cachePool->getItem('cache_pool_1_key_to_purge_1');
        $cachePool1Item1 = $this->cachePool->getItem('key_1');
        $cachePool1Item2 = $this->cachePool->getItem('key_2');
        $cachePool1Item3 = $this->cachePool->getItem('key_3');

        self::assertNull($cachePool1ItemAfterPurge1->get());
        self::assertEquals('value_1', $cachePool1Item1->get());
        self::assertEquals('value_2', $cachePool1Item2->get());
        self::assertEquals('value_3', $cachePool1Item3->get());
    }

    public function testLoadCachePoolFixturesWithAppend(): void
    {
        $cachePool1ItemToNotPurge1 = $this->cachePool->getItem('cache_pool_1_key_to_not_purge_1');
        $cachePool1ItemToNotPurge1->set('value_to_not_purge_1');
        $this->cachePool->save($cachePool1ItemToNotPurge1);

        $this->loadCachePoolFixtures(
            'app.cache.first',
            Options::create()->withAppend(),
            CachePoolFixture1::class,
            CachePoolFixture2::class
        );

        $cachePool1ItemAfterToNotPurge1 = $this->cachePool->getItem('cache_pool_1_key_to_not_purge_1');
        $cachePool1Item1 = $this->cachePool->getItem('key_1');
        $cachePool1Item2 = $this->cachePool->getItem('key_2');
        $cachePool1Item3 = $this->cachePool->getItem('key_3');

        self::assertEquals('value_to_not_purge_1', $cachePool1ItemAfterToNotPurge1->get());
        self::assertEquals('value_1', $cachePool1Item1->get());
        self::assertEquals('value_2', $cachePool1Item2->get());
        self::assertEquals('value_3', $cachePool1Item3->get());
    }

    public function testLoadTagAwareCachePoolFixturesWithoutAppend(): void
    {
        $cachePool1ItemToPurge1 = $this->tagAwareCachePool->getItem('cache_pool_1_key_to_purge_1');

        $cachePool1ItemToPurge1->set('value_to_purge_1');
        $this->tagAwareCachePool->save($cachePool1ItemToPurge1);

        $this->registerInitializableFixtureForCachePool(
            'app.cache.third',
            CachePoolFixture1::class,
            self::EXTRA_DATA_FOR_INIT
        );

        $this->loadCachePoolFixtures(
            'app.cache.third',
            Options::create(),
            CachePoolFixture1::class,
            CachePoolFixture2::class
        );

        $cachePool1ItemAfterPurge1 = $this->tagAwareCachePool->getItem('cache_pool_1_key_to_purge_1');
        $cachePool1Item1 = $this->tagAwareCachePool->getItem('key_1');
        $cachePool1Item2 = $this->tagAwareCachePool->getItem('key_2');
        $cachePool1Item3 = $this->tagAwareCachePool->getItem('key_3');

        self::assertNull($cachePool1ItemAfterPurge1->get());
        self::assertEquals('value_1', $cachePool1Item1->get());
        self::assertEquals('value_2', $cachePool1Item2->get());
        self::assertEquals('value_3', $cachePool1Item3->get());
    }

    public function testLoadTagAwareCachePoolFixturesWithAppend(): void
    {
        $cachePool1ItemToNotPurge1 = $this->tagAwareCachePool->getItem('cache_pool_1_key_to_not_purge_1');
        $cachePool1ItemToNotPurge1->set('value_to_not_purge_1');
        $this->tagAwareCachePool->save($cachePool1ItemToNotPurge1);

        $this->loadCachePoolFixtures(
            'app.cache.third',
            Options::create()->withAppend(),
            CachePoolFixture1::class,
            CachePoolFixture2::class
        );

        $cachePool1ItemAfterToNotPurge1 = $this->tagAwareCachePool->getItem('cache_pool_1_key_to_not_purge_1');
        $cachePool1Item1 = $this->tagAwareCachePool->getItem('key_1');
        $cachePool1Item2 = $this->tagAwareCachePool->getItem('key_2');
        $cachePool1Item3 = $this->tagAwareCachePool->getItem('key_3');

        self::assertEquals('value_to_not_purge_1', $cachePool1ItemAfterToNotPurge1->get());
        self::assertEquals('value_1', $cachePool1Item1->get());
        self::assertEquals('value_2', $cachePool1Item2->get());
        self::assertEquals('value_3', $cachePool1Item3->get());
    }

    public function testLoadTagAwarePoolCachePoolFixturesWithoutAppend(): void
    {
        $cachePool1ItemToPurge1 = $this->tagAwarePoolCachePool->getItem('cache_pool_1_key_to_purge_1');

        $cachePool1ItemToPurge1->set('value_to_purge_1');
        $this->tagAwarePoolCachePool->save($cachePool1ItemToPurge1);

        $this->registerInitializableFixtureForCachePool(
            'app.cache.fourth',
            CachePoolFixture1::class,
            self::EXTRA_DATA_FOR_INIT
        );

        $this->loadCachePoolFixtures(
            'app.cache.fourth',
            Options::create(),
            CachePoolFixture1::class,
            CachePoolFixture2::class
        );

        $cachePool1ItemAfterPurge1 = $this->tagAwarePoolCachePool->getItem('cache_pool_1_key_to_purge_1');
        $cachePool1Item1 = $this->tagAwarePoolCachePool->getItem('key_1');
        $cachePool1Item2 = $this->tagAwarePoolCachePool->getItem('key_2');
        $cachePool1Item3 = $this->tagAwarePoolCachePool->getItem('key_3');

        self::assertNull($cachePool1ItemAfterPurge1->get());
        self::assertEquals('value_1', $cachePool1Item1->get());
        self::assertEquals('value_2', $cachePool1Item2->get());
        self::assertEquals('value_3', $cachePool1Item3->get());
    }

    public function testLoadTagAwarePoolCachePoolFixturesWithAppend(): void
    {
        $cachePool1ItemToNotPurge1 = $this->tagAwarePoolCachePool->getItem('cache_pool_1_key_to_not_purge_1');
        $cachePool1ItemToNotPurge1->set('value_to_not_purge_1');
        $this->tagAwarePoolCachePool->save($cachePool1ItemToNotPurge1);

        $this->loadCachePoolFixtures(
            'app.cache.fourth',
            Options::create()->withAppend(),
            CachePoolFixture1::class,
            CachePoolFixture2::class
        );

        $cachePool1ItemAfterToNotPurge1 = $this->tagAwarePoolCachePool->getItem('cache_pool_1_key_to_not_purge_1');
        $cachePool1Item1 = $this->tagAwarePoolCachePool->getItem('key_1');
        $cachePool1Item2 = $this->tagAwarePoolCachePool->getItem('key_2');
        $cachePool1Item3 = $this->tagAwarePoolCachePool->getItem('key_3');

        self::assertEquals('value_to_not_purge_1', $cachePool1ItemAfterToNotPurge1->get());
        self::assertEquals('value_1', $cachePool1Item1->get());
        self::assertEquals('value_2', $cachePool1Item2->get());
        self::assertEquals('value_3', $cachePool1Item3->get());
    }

    public function testLoadChainCachePoolFixturesWithoutAppend(): void
    {
        $cachePool1ItemToPurge1 = $this->chainCachePool->getItem('cache_pool_1_key_to_purge_1');

        $cachePool1ItemToPurge1->set('value_to_purge_1');
        $this->chainCachePool->save($cachePool1ItemToPurge1);

        $this->registerInitializableFixtureForCachePool(
            'app.cache.fifth',
            CachePoolFixture1::class,
            self::EXTRA_DATA_FOR_INIT
        );

        $this->loadCachePoolFixtures(
            'app.cache.fifth',
            Options::create(),
            CachePoolFixture1::class,
            CachePoolFixture2::class
        );

        $cachePool1ItemAfterPurge1 = $this->chainCachePool->getItem('cache_pool_1_key_to_purge_1');
        $cachePool1Item1 = $this->chainCachePool->getItem('key_1');
        $cachePool1Item2 = $this->chainCachePool->getItem('key_2');
        $cachePool1Item3 = $this->chainCachePool->getItem('key_3');

        self::assertNull($cachePool1ItemAfterPurge1->get());
        self::assertEquals('value_1', $cachePool1Item1->get());
        self::assertEquals('value_2', $cachePool1Item2->get());
        self::assertEquals('value_3', $cachePool1Item3->get());
    }

    public function testLoadChainPoolCachePoolFixturesWithAppend(): void
    {
        $cachePool1ItemToNotPurge1 = $this->chainCachePool->getItem('cache_pool_1_key_to_not_purge_1');
        $cachePool1ItemToNotPurge1->set('value_to_not_purge_1');
        $this->chainCachePool->save($cachePool1ItemToNotPurge1);

        $this->loadCachePoolFixtures(
            'app.cache.fifth',
            Options::create()->withAppend(),
            CachePoolFixture1::class,
            CachePoolFixture2::class
        );

        $cachePool1ItemAfterToNotPurge1 = $this->chainCachePool->getItem('cache_pool_1_key_to_not_purge_1');
        $cachePool1Item1 = $this->chainCachePool->getItem('key_1');
        $cachePool1Item2 = $this->chainCachePool->getItem('key_2');
        $cachePool1Item3 = $this->chainCachePool->getItem('key_3');

        self::assertEquals('value_to_not_purge_1', $cachePool1ItemAfterToNotPurge1->get());
        self::assertEquals('value_1', $cachePool1Item1->get());
        self::assertEquals('value_2', $cachePool1Item2->get());
        self::assertEquals('value_3', $cachePool1Item3->get());
    }

    public function testClearFixtures(): void
    {
        $this->loadCachePoolFixtures(
            'app.cache.fifth',
            Options::create(),
            CachePoolFixture1::class,
            CachePoolFixture2::class
        );
        $this->clearCachePoolFixtures('app.cache.fifth');

        self::assertEmpty($this->getCachePoolFixtures('app.cache.fifth'));
    }

    protected function setUp(): void
    {
        $this->cachePool = $this->getCachePool('app.cache.first');
        $this->tagAwareCachePool = $this->getCachePool('app.cache.third');
        $this->tagAwarePoolCachePool = $this->getCachePool('app.cache.fourth');
        $this->chainCachePool = $this->getCachePool('app.cache.fifth');
    }
}
