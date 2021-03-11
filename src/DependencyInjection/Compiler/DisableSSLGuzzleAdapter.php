<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\DependencyInjection\Compiler;

use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\OutOfBoundsException;

final class DisableSSLGuzzleAdapter implements DisableSSLAdapter
{
    public function getClientClass(): string
    {
        return Client::class;
    }

    public function changeDefinition(Definition $definition): void
    {
        try {
            $args = $definition->getArgument(0);
        } catch (OutOfBoundsException $e) {
            $args = [];
        }

        $definition->setArgument(0, array_merge($args, ['verify' => false]));
    }
}
