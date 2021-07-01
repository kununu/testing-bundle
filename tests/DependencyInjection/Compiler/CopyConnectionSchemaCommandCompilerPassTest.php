<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\DependencyInjection\Compiler;

use Kununu\TestingBundle\Command\CopyConnectionSchemaCommand;
use Kununu\TestingBundle\DependencyInjection\Compiler\CopyConnectionSchemaCommandCompilerPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class CopyConnectionSchemaCommandCompilerPassTest extends AbstractCompilerPassTestCase
{
    private const COMMAND_ID = 'kununu_testing:connections:schema:copy.command';

    public function testCompile(): void
    {
        $this->setParameter('doctrine.connections', ['default' => 'doctrine.default_connection']);

        $this->compile();

        $this->assertContainerBuilderHasService(self::COMMAND_ID, CopyConnectionSchemaCommand::class);
        $this->assertContainerBuilderHasServiceDefinitionWithTag(self::COMMAND_ID, 'console.command');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(self::COMMAND_ID, 0, 'kununu_testing.schema_copy');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(self::COMMAND_ID, 1, 'doctrine');
        $this->assertTrue($this->container->getDefinition(self::COMMAND_ID)->isPublic());
    }

    public function testCompileWithoutDoctrineConnections(): void
    {
        $this->compile();

        $this->assertContainerBuilderNotHasService(self::COMMAND_ID);
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new CopyConnectionSchemaCommandCompilerPass());
    }
}
