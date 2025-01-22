<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Test;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use UnitEnum;

abstract class AbstractTestCase extends WebTestCase
{
    protected ?KernelBrowser $kernelBrowser = null;

    final protected function getFixturesContainer(): ContainerInterface
    {
        if (!static::$booted) {
            $this->getKernelBrowser();
        }

        return static::getContainer();
    }

    // @phpstan-ignore return.unusedType
    final protected function getServiceFromContainer(string $service): ?object
    {
        return $this->getFixturesContainer()->get($service);
    }

    final protected function getParameterFromContainer(string $name): UnitEnum|float|array|bool|int|string|null
    {
        return $this->getFixturesContainer()->getParameter($name);
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
        $this->ensureKernelShutdown();
        $this->kernelBrowser = null;
    }
}
