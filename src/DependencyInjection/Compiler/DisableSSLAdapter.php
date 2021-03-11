<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Definition;

interface DisableSSLAdapter
{
    public function getClientClass(): string;

    public function changeDefinition(Definition $definition): void;
}
