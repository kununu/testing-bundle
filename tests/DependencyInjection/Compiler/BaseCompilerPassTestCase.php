<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\DependencyInjection\Compiler;

use Kununu\TestingBundle\DependencyInjection\KununuTestingExtension;
use Kununu\TestingBundle\Service\Orchestrator;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Reference;

abstract class BaseCompilerPassTestCase extends AbstractCompilerPassTestCase
{
    protected function assertFixturesCommand(
        string $commandId,
        string $commandName,
        string $commandClassName,
        string $alias,
        string $orchestratorId,
        array $fixturesClassesNamespaces
    ): void {
        $this->assertContainerBuilderHasService(
            $commandId,
            $commandClassName
        );

        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            $commandId,
            'console.command',
            ['command' => $commandName]
        );

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            $commandId,
            0,
            $alias
        );

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            $commandId,
            1,
            new Reference($orchestratorId)
        );

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            $commandId,
            2,
            $fixturesClassesNamespaces
        );

        $this->assertTrue($this->container->getDefinition($orchestratorId)->isPublic());
    }

    protected function assertPurger(string $purgerId, string $purgerClass, mixed ...$arguments): void
    {
        $this->assertContainerBuilderHasService($purgerId, $purgerClass);
        $this->assertTrue($this->container->getDefinition($purgerId)->isPrivate());
        foreach ($arguments as $position => $argument) {
            $this->assertContainerBuilderHasServiceDefinitionWithArgument($purgerId, $position, $argument);
        }
    }

    protected function assertExecutor(string $executorId, string $executorClass, mixed ...$arguments): void
    {
        $this->assertContainerBuilderHasService($executorId, $executorClass);
        $this->assertTrue($this->container->getDefinition($executorId)->isPrivate());
        foreach ($arguments as $position => $argument) {
            $this->assertContainerBuilderHasServiceDefinitionWithArgument($executorId, $position, $argument);
        }
    }

    protected function assertLoader(string $loaderId, string $loaderClass): void
    {
        $this->assertContainerBuilderHasService($loaderId, $loaderClass);
        $this->assertTrue($this->container->getDefinition($loaderId)->isPrivate());
    }

    protected function assertOrchestrator(string $orchestratorId, string $executorId, string $loaderId): void
    {
        $this->assertContainerBuilderHasService($orchestratorId, Orchestrator::class);
        $this->assertTrue($this->container->getDefinition($orchestratorId)->isPublic());
        $this->assertContainerBuilderHasServiceDefinitionWithArgument($orchestratorId, 0, new Reference($executorId));
        $this->assertContainerBuilderHasServiceDefinitionWithArgument($orchestratorId, 1, new Reference($loaderId));
    }

    protected function getMockKununuTestingExtension(): ExtensionInterface
    {
        $mock = $this->createMock(ExtensionInterface::class);

        $mock
            ->expects($this->any())
            ->method('getAlias')
            ->willReturn(KununuTestingExtension::ALIAS);

        $mock
            ->expects($this->any())
            ->method('getNamespace')
            ->willReturn(false);

        return $mock;
    }
}
