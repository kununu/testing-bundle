<?php declare(strict_types=1);

namespace Kununu\TestingBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

final class KununuTestingExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);

        if (!empty($config['connections'])) {
            foreach ($config['connections'] as $connId => $connectionConfigs) {
                $container->setParameter(
                    sprintf('kununu_testing.connections.%s', $connId),
                    $connectionConfigs
                );
            }
        }

        if (!empty($config['elastic_search'])) {
            $container->setParameter('kununu_testing.elastic_search', $config['elastic_search']);
        }
    }
}
