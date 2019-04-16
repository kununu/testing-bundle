<?php declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\App\Fixtures\CachePool;

use Kununu\DataFixtures\Adapter\CachePoolFixtureInterface;
use Psr\Cache\CacheItemPoolInterface;

final class CachePoolFixture1 implements CachePoolFixtureInterface
{
    public function load(CacheItemPoolInterface $cachePool): void
    {
        $item1 = $cachePool->getItem('key_1');
        $item1->set('value_1');
        $cachePool->save($item1);

        $item1 = $cachePool->getItem('key_2');
        $item1->set('value_2');
        $cachePool->save($item1);
    }
}
