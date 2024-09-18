<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\App\Fixtures\CachePool;

use Kununu\DataFixtures\Adapter\CachePoolFixtureInterface;
use Psr\Cache\CacheItemPoolInterface;

final readonly class CachePoolFixture2 implements CachePoolFixtureInterface
{
    public function load(CacheItemPoolInterface $cachePool): void
    {
        $item = $cachePool->getItem('key_3');
        $item->set('value_3');
        $cachePool->save($item);
    }
}
