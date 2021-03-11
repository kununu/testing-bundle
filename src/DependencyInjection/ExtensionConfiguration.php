<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\DependencyInjection;

interface ExtensionConfiguration
{
    public function getConfig(): array;
}
