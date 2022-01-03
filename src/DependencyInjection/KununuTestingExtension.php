<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class KununuTestingExtension extends Extension implements ExtensionConfiguration
{
    public const ALIAS = 'kununu_testing';

    private $config = [];

    public function load(array $configs, ContainerBuilder $container): void
    {
        $this->config = $this->processConfiguration(new Configuration(), $configs);
        (new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config')))->load('services.yaml');

        foreach (['connections', 'non_transactional_connections'] as $section) {
            if (!empty($this->config[$section])) {
                foreach ($this->config[$section] as $connId => $connectionConfigs) {
                    $container->setParameter(
                        sprintf('kununu_testing.%s.%s', $section, $connId),
                        $connectionConfigs
                    );
                }
            }
        }

        if (!empty($this->config['elastic_search'])) {
            $container->setParameter('kununu_testing.elastic_search', $this->config['elastic_search']);
        }
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
