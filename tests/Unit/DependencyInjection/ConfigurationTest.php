<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Unit\DependencyInjection;

final class ConfigurationTest extends ConfigurationTestCase
{
    public static function validProcessedConfigurationDataProvider(): array
    {
        return [
            'empty_configuration' => [
                [],
                [
                    'connections'                   => [],
                    'non_transactional_connections' => [],
                    'elastic_search'                => [],
                    'cache'                         => [
                        'enable' => true,
                        'pools'  => [],
                    ],
                    'http_client'                   => [
                        'clients' => [],
                    ],
                    'open_search'                   => [],
                ],
            ],
        ];
    }

    protected function getNodeName(): null
    {
        return null;
    }
}
