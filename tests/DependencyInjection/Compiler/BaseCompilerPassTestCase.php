<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\DependencyInjection\Compiler;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
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
}
