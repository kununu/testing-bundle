<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Unit;

use Kununu\TestingBundle\DependencyInjection\Compiler\CachePoolCompilerPass;
use Kununu\TestingBundle\DependencyInjection\Compiler\ConnectionCompilerPass;
use Kununu\TestingBundle\DependencyInjection\Compiler\CopyConnectionSchemaCommandCompilerPass;
use Kununu\TestingBundle\DependencyInjection\Compiler\ElasticsearchCompilerPass;
use Kununu\TestingBundle\DependencyInjection\Compiler\HttpClientCompilerPass;
use Kununu\TestingBundle\DependencyInjection\Compiler\NonTransactionalConnectionCompilerPass;
use Kununu\TestingBundle\KununuTestingBundle;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class KununuTestingBundleTest extends TestCase
{
    public function testBuildContainerExpectedCompilerPasses(): void
    {
        $executedCompilerPasses = [];

        $container = $this->createMock(ContainerBuilder::class);
        $container
            ->expects(self::exactly(6))
            ->method('addCompilerPass')
            ->willReturnCallback(
                function($subject) use ($container, &$executedCompilerPasses): MockObject&ContainerBuilder {
                    $executedCompilerPasses[] = $subject::class;

                    return $container;
                }
            );

        (new KununuTestingBundle())->build($container);

        self::assertEquals(
            [
                CachePoolCompilerPass::class,
                ConnectionCompilerPass::class,
                NonTransactionalConnectionCompilerPass::class,
                ElasticsearchCompilerPass::class,
                HttpClientCompilerPass::class,
                CopyConnectionSchemaCommandCompilerPass::class,
            ],
            $executedCompilerPasses
        );
    }
}
