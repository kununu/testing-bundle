<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Test;

use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractTestCase extends WebTestCase
{
    protected ?KernelBrowser $kernelBrowser = null;

    final protected function getFixturesContainer(): ContainerInterface
    {
        if (
            !static::$kernel ||
            !(method_exists(static::class, 'getContainer') ? static::getContainer() : static::$container)
        ) {
            $this->getKernelBrowser();
        }

        return method_exists(static::class, 'getContainer') ? static::getContainer() : static::$container;
    }

    final protected function getKernelBrowser(): KernelBrowser
    {
        if (!$this->kernelBrowser) {
            $this->kernelBrowser = static::createClient();
        }

        return $this->kernelBrowser;
    }

    final protected function shutdown(): void
    {
        $this->kernelBrowser = null;
        $this->ensureKernelShutdown();
    }
}
