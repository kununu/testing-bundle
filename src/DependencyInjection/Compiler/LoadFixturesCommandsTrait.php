<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

trait LoadFixturesCommandsTrait
{
    private function buildLoadFixturesCommand(
        ContainerBuilder $container,
        string $orchestratorId,
        string $commandClassName,
        string $alias,
        array $fixturesClassesNamespace
    ): void {
        if (empty($fixturesClassesNamespace)) {
            return;
        }

        $loadFixturesCommandDefinition = new Definition(
            $commandClassName,
            [
                $alias,
                new Reference($orchestratorId),
                $fixturesClassesNamespace,
            ]
        );

        $commandName = call_user_func([$commandClassName, 'getNameByAlias'], $alias);

        $loadFixturesCommandDefinition->setPublic(true);
        $loadFixturesCommandDefinition->setTags([
            'console.command' => [
                [
                    'command' => $commandName,
                ],
            ],
        ]);

        $container->setDefinition(
            str_replace(':', '.', sprintf('%s.command', $commandName)),
            $loadFixturesCommandDefinition
        );
    }
}
