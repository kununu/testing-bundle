<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\DependencyInjection;

final class ConfigurationTest extends ConfigurationTestCase
{
    public function validProcessedConfigurationDataProvider(): array
    {
        return [
            'empty_configuration' => [
                [],
                [
                    'connections'    => [],
                    'elastic_search' => [],
                    'cache'          => [
                        'enable' => true,
                        'pools'  => [],
                    ],
                    'http_client'    => [
                        'clients' => [],
                    ],
                ],
            ],
        ];
    }

    protected function getNodeName(): ?string
    {
        return null;
    }
}
