<?php declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Service;

use Kununu\DataFixtures\Executor\ExecutorInterface;
use Kununu\DataFixtures\FixtureInterface;
use Kununu\DataFixtures\Loader\LoaderInterface;
use Kununu\DataFixtures\Purger\PurgerInterface;
use Kununu\TestingBundle\Service\Orchestrator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class OrchestratorTest extends TestCase
{
    /**
     * @dataProvider executesAsExpectedDataProvider
     *
     * @param bool $append
     */
    public function testThatExecutesAsExpected(bool $append): void
    {
        $fixture1 = $this->getMockBuilder(FixtureInterface::class)->setMockClassName('Mock1')->getMock();
        $fixture2 = $this->getMockBuilder(FixtureInterface::class)->setMockClassName('Mock2')->getMock();

        /** @var LoaderInterface|MockObject $loader */
        $loader = $this->createMock(LoaderInterface::class);
        $loader
            ->expects($this->exactly(2))
            ->method('loadFromClassName')
            ->withConsecutive(['Mock1'], ['Mock2']);
        $loader
            ->expects($this->once())
            ->method('getFixtures')
            ->willReturn([$fixture1, $fixture2]);
        $loader
            ->expects($this->once())
            ->method('clearFixtures');

        /** @var ExecutorInterface|MockObject $executor */
        $executor = $this->createMock(ExecutorInterface::class);
        $executor
            ->expects($this->once())
            ->method('execute')
            ->with([$fixture1, $fixture2], $append);

        /** @var PurgerInterface|MockObject $purger */
        $purger = $this->createMock(PurgerInterface::class);

        $orchestrator = new Orchestrator($executor, $purger, $loader);

        $orchestrator->execute(['Mock1', 'Mock2'], $append);
        $orchestrator->clearFixtures();
    }

    public function executesAsExpectedDataProvider()
    {
        return [
            'with_append'    => [true],
            'without_append' => [false],
        ];
    }
}
