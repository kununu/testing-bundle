<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\DependencyInjection;

interface ExtensionConfigurationInterface
{
    public function getConfig(): array;
}
