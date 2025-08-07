<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Unit\Service;

use DateTime;
use Kununu\DataFixtures\Executor\ExecutorInterface;
use Kununu\DataFixtures\FixtureInterface;
use Kununu\DataFixtures\Loader\LoaderInterface;
use Kununu\TestingBundle\Service\Orchestrator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class OrchestratorTest extends TestCase
{
    #[DataProvider('executesAsExpectedDataProvider')]
    public function testThatExecutesAsExpected(bool $append): void
    {
        $fixture1 = $this->getNamedFixtureMock($fixture1Class = $this->generateMockClassName('Mock1'));
        $fixture2 = $this->getNamedFixtureMock($fixture2Class = $this->generateMockClassName('Mock2'));

        $loader = $this->createMock(LoaderInterface::class);
        $loader
            ->expects($this->exactly(2))
            ->method('loadFromClassName')
            ->with(
                $this->callback(fn(string $class): bool => match ($class) {
                    $fixture1Class, $fixture2Class => true,
                    default                        => false,
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

        $orchestrator->execute([$fixture1Class, $fixture2Class], $append);
    }

    public static function executesAsExpectedDataProvider(): array
    {
        return [
            'with_append'    => [true],
            'without_append' => [false],
        ];
    }

    private function getNamedFixtureMock(string $name): MockObject&FixtureInterface
    {
        return $this
            ->getMockBuilder(FixtureInterface::class)
            ->setMockClassName($name)
            ->getMock();
    }

    private function generateMockClassName(string $prefix): string
    {
        return sprintf('%s%s', $prefix, md5((new DateTime())->format('Y-m-d H:i:s.uP')));
    }
}
