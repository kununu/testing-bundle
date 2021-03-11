<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\DependencyInjection\Compiler;

use Kununu\TestingBundle\DependencyInjection\ExtensionConfiguration;
use Kununu\TestingBundle\DependencyInjection\KununuTestingExtension;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

final class DisableSSLCompilerPass implements CompilerPassInterface
{
    private $adapters;
    private $enable = false;
    private $hostEnvVar;
    private $host;
    private $clients = [];
    private $domains = [];

    public function __construct(DisableSSLAdapter ...$adapters)
    {
        $this->adapters = $adapters;
    }

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasExtension(KununuTestingExtension::ALIAS)) {
            return;
        }

        $extension = $container->getExtension(KununuTestingExtension::ALIAS);
        if (!$extension instanceof ExtensionConfiguration) {
            return;
        }

        $this->parseConfig($extension->getConfig());

        if (!$this->enable || !$this->isRunningOnTestHost()) {
            return;
        }

        foreach ($this->clients as $clientId) {
            try {
                $client = $container->getDefinition($clientId);
                if (!is_string($class = $client->getClass()) || $this->changedClientDefinition($client, $class)) {
                    continue;
                }
            } catch (ServiceNotFoundException $e) {
                continue;
            }
        }
    }

    private function changedClientDefinition(Definition $clientDefinition, string $class): bool
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->getClientClass() === $class) {
                $adapter->changeDefinition($clientDefinition);

                return true;
            }
        }

        return false;
    }

    private function isRunningOnTestHost(): bool
    {
        $hostName = $this->host();

        if ('' === $hostName) {
            return false;
        }

        foreach ($this->domains as $domain) {
            if ($this->endsWith($hostName, $domain)) {
                return true;
            }
        }

        return false;
    }

    private function host(): string
    {
        if (null === $this->host) {
            $this->host = trim(getenv($this->hostEnvVar) ?: '');
        }

        return $this->host;
    }

    private function parseConfig(array $config): void
    {
        $config = $config['ssl_check_disable'] ?? [];

        $this->enable = (bool) ($config['enable'] ?? false);
        $this->clients = array_unique($config['clients'] ?? []);
        $this->domains = array_unique($config['domains'] ?? []);
        $this->hostEnvVar = $config['env_var'] ?? 'VIRTUAL_HOST';
    }

    private function endsWith(string $haystack, string $needle): bool
    {
        return substr($haystack, -strlen($needle)) === $needle;
    }
}
