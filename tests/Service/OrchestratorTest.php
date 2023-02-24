<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Service;

use Kununu\DataFixtures\Executor\ExecutorInterface;
use Kununu\DataFixtures\FixtureInterface;
use Kununu\DataFixtures\Loader\LoaderInterface;
use Kununu\TestingBundle\Service\Orchestrator;
use PHPUnit\Framework\TestCase;

final class OrchestratorTest extends TestCase
{
    /** @dataProvider executesAsExpectedDataProvider */
    public function testThatExecutesAsExpected(bool $append): void
    {
        $fixture1 = $this->getMockBuilder(FixtureInterface::class)->setMockClassName('Mock1')->getMock();
        $fixture2 = $this->getMockBuilder(FixtureInterface::class)->setMockClassName('Mock2')->getMock();

        $loader = $this->createMock(LoaderInterface::class);
        $loader
            ->expects($this->exactly(2))
            ->method('loadFromClassName')
            ->with(
                $this->callback(fn(string $class): bool => match ($class) {
                    'Mock1', 'Mock2' => true,
                    default => false
                })
            );
        $loader
            ->expects($this->once())
            ->method('getFixtures')
            ->willReturn([$fixture1, $fixture2]);
        $loader
            ->expects($this->once())
            ->method('clearFixtures');

        $executor = $this->createMock(ExecutorInterface::class);
        $executor
            ->expects($this->once())
            ->method('execute')
            ->with([$fixture1, $fixture2], $append);

        $orchestrator = new Orchestrator($executor, $loader);

        $orchestrator->execute(['Mock1', 'Mock2'], $append);
    }

    public static function executesAsExpectedDataProvider(): array
    {
        return [
            'with_append'    => [true],
            'without_append' => [false],
        ];
    }
}
