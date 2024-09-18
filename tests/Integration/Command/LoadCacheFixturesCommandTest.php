<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Integration\Command;

use Kununu\TestingBundle\Command\LoadCacheFixturesCommand;
use Kununu\TestingBundle\Test\Options\Options;
use Kununu\TestingBundle\Tests\App\Fixtures\CachePool\CachePoolFixture3;
use Psr\Cache\CacheItemPoolInterface;

final class LoadCacheFixturesCommandTest extends AbstractFixturesCommandTestCase
{
    private const string COMMAND_1 = 'kununu_testing:load_fixtures:cache_pools:app.cache.first';
    private const string COMMAND_2 = 'kununu_testing:load_fixtures:cache_pools:app.cache.second';

    private CacheItemPoolInterface $cachePool;

    protected function doAssertionsForExecuteAppend(): void
    {
        self::assertCacheValue($this->cachePool, true, 'key_1', 'value_1');
        self::assertCacheValue($this->cachePool, true, 'key_2', 'value_2');
        self::assertCacheValue($this->cachePool, true, 'key_3', 'value_3');
        self::assertCacheValue($this->cachePool, true, 'existing_key', 'existing_value');
    }

    protected function doAssertionsForExecuteNonAppendInteractive(): void
    {
        self::assertCacheValue($this->cachePool, true, 'key_1', 'value_1');
        self::assertCacheValue($this->cachePool, true, 'key_2', 'value_2');
        self::assertCacheValue($this->cachePool, true, 'key_3', 'value_3');
        self::assertCacheValue($this->cachePool, false, 'existing_key');
    }

    protected function doAssertionsForExecuteNonAppendNonInteractive(): void
    {
        self::assertCacheValue($this->cachePool, true, 'key_1', 'value_1');
        self::assertCacheValue($this->cachePool, true, 'key_2', 'value_2');
        self::assertCacheValue($this->cachePool, true, 'key_3', 'value_3');
        self::assertCacheValue($this->cachePool, false, 'existing_key');
    }

    protected function getCommandClass(): string
    {
        return LoadCacheFixturesCommand::class;
    }

    protected function getExistingCommandAlias(): string
    {
        return self::COMMAND_1;
    }

    protected function getNonExistingCommandAliases(): array
    {
        return [self::COMMAND_2];
    }

    protected function preRunCommand(): void
    {
        $this->loadCachePoolFixtures('app.cache.first', Options::create(), CachePoolFixture3::class);

        self::assertCacheValue($this->cachePool, false, 'key_1');
        self::assertCacheValue($this->cachePool, false, 'key_2');
        self::assertCacheValue($this->cachePool, false, 'key_3');
        self::assertCacheValue($this->cachePool, true, 'existing_key', 'existing_value');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->cachePool = $this->getCachePool('app.cache.first');
    }

    private static function assertCacheValue(
        CacheItemPoolInterface $cachePool,
        bool $exists,
        string $key,
        $value = null,
    ): void {
        if ($exists) {
            self::assertTrue($cachePool->hasItem($key));
            self::assertEquals($value, $cachePool->getItem($key)->get());
        } else {
            self::assertFalse($cachePool->hasItem($key));
            self::assertNull($cachePool->getItem($key)->get());
        }
    }
}
