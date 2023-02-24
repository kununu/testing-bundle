<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\DependencyInjection\Compiler;

use Kununu\TestingBundle\DependencyInjection\ExtensionConfigurationInterface;
use Kununu\TestingBundle\DependencyInjection\KununuTestingExtension;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

abstract class AbstractCompilerPass implements CompilerPassInterface
{
    protected function getExtensionConfiguration(ContainerBuilder $containerBuilder): ?array
    {
        $extension = $containerBuilder->hasExtension(KununuTestingExtension::ALIAS)
            ? $containerBuilder->getExtension(KununuTestingExtension::ALIAS)
            : null;

        return $extension instanceof ExtensionConfigurationInterface ? $extension->getConfig() : null;
    }
}
