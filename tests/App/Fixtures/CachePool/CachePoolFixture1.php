<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\App\Fixtures\CachePool;

use Kununu\DataFixtures\Adapter\CachePoolFixtureInterface;
use Kununu\DataFixtures\InitializableFixtureInterface;
use Psr\Cache\CacheItemPoolInterface;

final class CachePoolFixture1 implements CachePoolFixtureInterface, InitializableFixtureInterface
{
    private mixed $arg1;

    public function load(CacheItemPoolInterface $cachePool): void
    {
        $item1 = $cachePool->getItem('key_1');
        $item1->set('value_1');
        $cachePool->save($item1);

        $item1 = $cachePool->getItem('key_2');
        $item1->set('value_2');
        $cachePool->save($item1);
    }

    public function initializeFixture(mixed ...$args): void
    {
        if (count($args)) {
            $this->arg1 = $args[0];
        }
    }

    public function arg1(): mixed
    {
        return $this->arg1;
    }
}
