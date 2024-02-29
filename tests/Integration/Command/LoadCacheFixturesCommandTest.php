<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Integration\Command;

use Kununu\TestingBundle\Tests\App\Fixtures\CachePool\CachePoolFixture3;
use Kununu\TestingBundle\Command\LoadCacheFixturesCommand;
use Kununu\TestingBundle\Test\Options\Options;
use Psr\Cache\CacheItemPoolInterface;

final class LoadCacheFixturesCommandTest extends AbstractFixturesCommandTestCase
{
    private const COMMAND_1 = 'kununu_testing:load_fixtures:cache_pools:app.cache.first';
    private const COMMAND_2 = 'kununu_testing:load_fixtures:cache_pools:app.cache.second';

    private CacheItemPoolInterface $cachePool;

    protected function doAssertionsForExecuteAppend(): void
    {
        $this->assertCacheValue(true, 'key_1', 'value_1');
        $this->assertCacheValue(true, 'key_2', 'value_2');
        $this->assertCacheValue(true, 'key_3', 'value_3');
        $this->assertCacheValue(true, 'existing_key', 'existing_value');
    }

    protected function doAssertionsForExecuteNonAppendInteractive(): void
    {
        $this->assertCacheValue(true, 'key_1', 'value_1');
        $this->assertCacheValue(true, 'key_2', 'value_2');
        $this->assertCacheValue(true, 'key_3', 'value_3');
        $this->assertCacheValue(false, 'existing_key');
    }

    protected function doAssertionsForExecuteNonAppendNonInteractive(): void
    {
        $this->assertCacheValue(true, 'key_1', 'value_1');
        $this->assertCacheValue(true, 'key_2', 'value_2');
        $this->assertCacheValue(true, 'key_3', 'value_3');
        $this->assertCacheValue(false, 'existing_key');
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

        $this->assertCacheValue(false, 'key_1');
        $this->assertCacheValue(false, 'key_2');
        $this->assertCacheValue(false, 'key_3');
        $this->assertCacheValue(true, 'existing_key', 'existing_value');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->cachePool = $this->getFixturesContainer()->get('app.cache.first');
    }

    private function assertCacheValue(bool $exists, string $key, $value = null): void
    {
        if ($exists) {
            $this->assertTrue($this->cachePool->hasItem($key));
            $this->assertEquals($value, $this->cachePool->getItem($key)->get());
        } else {
            $this->assertFalse($this->cachePool->hasItem($key));
            $this->assertNull($this->cachePool->getItem($key)->get());
        }
    }
}
