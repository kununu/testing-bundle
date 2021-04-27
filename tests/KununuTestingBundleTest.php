<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests;

use Kununu\TestingBundle\DependencyInjection\Compiler\CachePoolCompilerPass;
use Kununu\TestingBundle\DependencyInjection\Compiler\ConnectionCompilerPass;
use Kununu\TestingBundle\DependencyInjection\Compiler\ElasticSearchCompilerPass;
use Kununu\TestingBundle\KununuTestingBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class KununuTestingBundleTest extends TestCase
{
    public function testBuild(): void
    {
        $containerMock = $this->createMock(ContainerBuilder::class);
        $containerMock
            ->expects($this->exactly(3))
            ->method('addCompilerPass')
            ->withConsecutive(
                [
                    $this->callback(function($subject) {
                        return $subject instanceof CachePoolCompilerPass;
                    }),
                ],
                [
                    $this->callback(function($subject) {
                        return $subject instanceof ConnectionCompilerPass;
                    }),
                ],
                [
                    $this->callback(function($subject) {
                        return $subject instanceof ElasticSearchCompilerPass;
                    }),
                ]
            );

        $bundle = new KununuTestingBundle();

        $bundle->build($containerMock);
    }
}
