<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Unit\DependencyInjection;

final class HttpClientConfigurationTest extends ConfigurationTestCase
{
    public static function validProcessedConfigurationDataProvider(): array
    {
        return [
            'no_configuration'   => [
                [
                    [],
                ],
                [
                    'http_client' => [
                        'clients' => [],
                    ],
                ],
            ],
            'with_configuration' => [
                [
                    [
                        'http_client' => [
                            'clients' => [
                                'http_client_1',
                                'http_client_2',
                            ],
                        ],
                    ],
                ],
                [
                    'http_client' => [
                        'clients' => [
                            'http_client_1',
                            'http_client_2',
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function getNodeName(): string
    {
        return 'http_client';
    }
}
