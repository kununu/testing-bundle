<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\DependencyInjection\Compiler;

use Kununu\TestingBundle\Command\CopyConnectionSchemaCommand;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class CopyConnectionSchemaCommandCompilerPass extends AbstractCompilerPass
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('doctrine.connections')) {
            return;
        }

        $definition = new Definition(
            CopyConnectionSchemaCommand::class,
            [
                new Reference('kununu_testing.schema_copy'),
                new Reference('doctrine'),
            ]
        );

        $container->setDefinition(
            'kununu_testing:connections:schema:copy.command',
            $definition->setPublic(true)->addTag('console.command')
        );
    }
}
