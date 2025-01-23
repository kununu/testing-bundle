<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\DependencyInjection\Compiler;

use Kununu\TestingBundle\DependencyInjection\ExtensionConfigurationInterface;
use Kununu\TestingBundle\DependencyInjection\KununuTestingExtension;
use Kununu\TestingBundle\Service\Orchestrator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

abstract class AbstractCompilerPass implements CompilerPassInterface
{
    protected function getExtensionConfiguration(ContainerBuilder $containerBuilder): ?array
    {
        $extension = $containerBuilder->hasExtension(KununuTestingExtension::ALIAS)
            ? $containerBuilder->getExtension(KununuTestingExtension::ALIAS)
            : null;

        return $extension instanceof ExtensionConfigurationInterface ? $extension->getConfig() : null;
    }

    protected function registerOrchestrator(
        ContainerBuilder $container,
        string $baseId,
        string $loaderId,
        string $orchestratorId,
        callable $purgerDefinitionBuilder,
        callable $executorDefinitionBuilder,
        string $loaderClass,
    ): void {
        // Create and register Purger definition
        [$purgerId, $purgerDefinition] = $purgerDefinitionBuilder($container, $baseId);
        $container->setDefinition($purgerId, $purgerDefinition);

        // Create and register Executor definition
        [$executorId, $executorDefinition] = $executorDefinitionBuilder($container, $baseId, $purgerId);
        $container->setDefinition($executorId, $executorDefinition);

        // Create and register Loader definition
        $loaderDefinition = new Definition($loaderClass);
        $container->setDefinition($loaderId, $loaderDefinition);

        // Create Orchestrator definition
        $orchestratorDefinition = new Definition(
            Orchestrator::class,
            [
                new Reference($executorId),
                new Reference($loaderId),
            ]
        );

        // Register Orchestrator definition (it will be public)
        $container
            ->setDefinition($orchestratorId, $orchestratorDefinition)
            ->setPublic(true);
    }
}
