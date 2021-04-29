<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\App\Fixtures\CachePool;

use Kununu\DataFixtures\Adapter\CachePoolFixtureInterface;
use Psr\Cache\CacheItemPoolInterface;

final class CachePoolFixture3 implements CachePoolFixtureInterface
{
    public function load(CacheItemPoolInterface $cachePool): void
    {
        $item = $cachePool->getItem('existing_key');
        $item->set('existing_value');
        $cachePool->save($item);
    }
}
