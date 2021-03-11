<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\DependencyInjection\Compiler;

use GuzzleHttp\Client;
use Kununu\TestingBundle\DependencyInjection\Compiler\DisableSSLCompilerPass;
use Kununu\TestingBundle\DependencyInjection\Compiler\DisableSSLGuzzleAdapter;
use Kununu\TestingBundle\DependencyInjection\KununuTestingExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use stdClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

final class DisableSSLCompilerPassTest extends AbstractCompilerPassTestCase
{
    private const SSL_VALIDATION_DISABLED = ['verify' => false];

    private const CLIENT_1_ID = 'app.guzzle.client';
    private const CLIENT_2_ID = 'app.guzzle.client.configured';
    private const CLIENT_3_ID = 'app.not_guzzle.client';

    private const ARGS_CLIENT_2 = [
        'base_uri' => 'https://www.kununu.com',
        'timeout'  => 2.0,
    ];

    /**
     * @dataProvider compilerPassDataProvider
     *
     * @param ExtensionInterface|null $extension
     * @param array                   $config
     * @param string                  $hostName
     * @param array                   $expectedClientsArgs
     * @param string                  $envVar
     */
    public function testCompilerPass(
        ?ExtensionInterface $extension,
        array $config,
        string $hostName,
        array $expectedClientsArgs,
        string $envVar = 'VIRTUAL_HOST'
    ): void {
        putenv(sprintf('%s=%s', $envVar, $hostName));

        if ($extension instanceof ExtensionInterface) {
            $this->container->registerExtension($extension);
            $this->container->loadFromExtension(KununuTestingExtension::ALIAS, $config);
        }

        $this->compile();

        foreach ($expectedClientsArgs as $clientId => $args) {
            $this->assertContainerBuilderHasService($clientId, Client::class);
            if (null !== $args) {
                $this->assertContainerBuilderHasServiceDefinitionWithArgument($clientId, 0, $args);
            }
        }
    }

    public function compilerPassDataProvider(): array
    {
        $clientUnchangedArgs = [
            self::CLIENT_1_ID => null,
            self::CLIENT_2_ID => self::ARGS_CLIENT_2,
        ];

        $clientChangedArgs = [
            self::CLIENT_1_ID => self::SSL_VALIDATION_DISABLED,
            self::CLIENT_2_ID => array_merge(self::ARGS_CLIENT_2, self::SSL_VALIDATION_DISABLED),
        ];

        $extension = new KununuTestingExtension();

        $invalidExtension = $this->createMock(ExtensionInterface::class);
        $invalidExtension
            ->expects($this->any())
            ->method('getAlias')
            ->willReturn(KununuTestingExtension::ALIAS);

        return [
            'no_extension_loaded'                                => [
                null,
                [],
                '',
                $clientUnchangedArgs,
            ],
            'invalid_exception'                                  => [
                $invalidExtension,
                [],
                '',
                $clientUnchangedArgs,
            ],
            'extension_no_config'                                => [
                $extension,
                [],
                '',
                $clientUnchangedArgs,
            ],
            'extension_config_dont_disable_ssl'                  => [
                $extension,
                [
                    'ssl_check_disable' => [
                        'enable' => false,
                    ],
                ],
                '',
                $clientUnchangedArgs,
            ],
            'extension_config_disable_ssl_no_domains_configured' => [
                $extension,
                [
                    'ssl_check_disable' => [
                        'enable' => true,
                    ],
                ],
                'host.kununu.it',
                $clientUnchangedArgs,
            ],
            'extension_config_disable_ssl_no_host'               => [
                $extension,
                [
                    'ssl_check_disable' => [
                        'enable' => true,
                    ],
                ],
                '',
                $clientUnchangedArgs,
            ],
            'extension_config_disable_ssl_no_host_match'         => [
                $extension,
                [
                    'ssl_check_disable' => [
                        'enable'  => true,
                        'domains' => [
                            'kununu.com',
                        ],
                    ],
                ],
                'host.kununu.it',
                $clientUnchangedArgs,
            ],
            'extension_config_disable_ssl_host_match'            => [
                $extension,
                [
                    'ssl_check_disable' => [
                        'enable'  => true,
                        'clients' => [
                            self::CLIENT_1_ID,
                            self::CLIENT_1_ID,
                            self::CLIENT_3_ID,
                            self::CLIENT_2_ID,
                            'non.existent.service',
                            self::CLIENT_3_ID,
                        ],
                        'domains' => [
                            'kununu.it',
                        ],
                        'env_var' => 'CUSTOM_ENV_VAR',
                    ],
                ],
                'host.kununu.it',
                $clientChangedArgs,
                'CUSTOM_ENV_VAR',
            ],
        ];
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new DisableSSLCompilerPass(
            new DisableSSLGuzzleAdapter()
        ));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->container->setDefinition(self::CLIENT_1_ID, new Definition(Client::class));
        $this->container->setDefinition(self::CLIENT_2_ID, (new Definition(Client::class))->setArgument(0, self::ARGS_CLIENT_2));
        $this->container->setDefinition(self::CLIENT_3_ID, new Definition(stdClass::class));
    }
}
