<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class KununuTestingExtension extends Extension implements ExtensionConfigurationInterface
{
    public const string ALIAS = 'kununu_testing';

    private const array CONNECTIONS = [
        'connections',
        'non_transactional_connections',
    ];

    private const array SEARCH_ENGINES = [
        'elastic_search',
        'open_search',
    ];

    private array $config = [];

    public function load(array $configs, ContainerBuilder $container): void
    {
        $this->config = $this->processConfiguration(new Configuration(), $configs);
        (new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config')))->load('services.yaml');

        foreach (self::CONNECTIONS as $section) {
            if (!empty($this->config[$section])) {
                foreach ($this->config[$section] as $connId => $connectionConfigs) {
                    $container->setParameter(
                        sprintf('kununu_testing.%s.%s', $section, $connId),
                        $connectionConfigs
                    );
                }
            }
        }
        foreach (self::SEARCH_ENGINES as $section) {
            if (!empty($this->config[$section])) {
                $container->setParameter(sprintf('kununu_testing.%s', $section), $this->config[$section]);
            }
        }
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
