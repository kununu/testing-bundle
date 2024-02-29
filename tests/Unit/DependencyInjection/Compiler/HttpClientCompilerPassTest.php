<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Unit\DependencyInjection\Compiler;

use Kununu\DataFixtures\Executor\HttpClientExecutor;
use Kununu\DataFixtures\Loader\HttpClientFixturesLoader;
use Kununu\DataFixtures\Purger\HttpClientPurger;
use Kununu\TestingBundle\DependencyInjection\Compiler\HttpClientCompilerPass;
use Kununu\TestingBundle\DependencyInjection\KununuTestingExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class HttpClientCompilerPassTest extends BaseCompilerPassTestCase
{
    private const HTTP_CLIENT_IDS = [
        'http_client_1',
        'http_client_2',
    ];

    private const CONFIG = [
        'http_client' => [
            'clients' => self::HTTP_CLIENT_IDS,
        ],
    ];

    public function testThatOrchestratorIsCreated(): void
    {
        $this->container->loadFromExtension(KununuTestingExtension::ALIAS, self::CONFIG);

        $this->doAssertionsOnHttpClientServices(
            function(
                string $purgerId,
                string $executorId,
                string $loaderId,
                string $orchestratorId,
                string $httpClientId
            ): void {
                $this->assertPurger($purgerId, HttpClientPurger::class, new Reference($httpClientId));
                $this->assertExecutor(
                    $executorId,
                    HttpClientExecutor::class,
                    new Reference($httpClientId),
                    new Reference($purgerId)
                );
                $this->assertLoader($loaderId, HttpClientFixturesLoader::class);
                $this->assertOrchestrator($orchestratorId, $executorId, $loaderId);
            }
        );
    }

    public function testThatOrchestratorIsNotCreatedWhenNoClientsAreProvided(): void
    {
        $this->assertNoOrchestratorIsCreated();
    }

    public function testThatOrchestratorIsNotCreatedWhenExtensionIsNotKununuTestingExtension(): void
    {
        $this->container->registerExtension($this->getMockKununuTestingExtension());
        $this->container->loadFromExtension(KununuTestingExtension::ALIAS, self::CONFIG);

        $this->assertNoOrchestratorIsCreated();
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new HttpClientCompilerPass());
        $container->registerExtension(new KununuTestingExtension());
    }

    private function assertNoOrchestratorIsCreated(): void
    {
        $this->doAssertionsOnHttpClientServices(
            function(string $purgerId, string $executorId, string $loaderId, string $orchestratorId): void {
                $this->assertContainerBuilderNotHasService($purgerId);
                $this->assertContainerBuilderNotHasService($executorId);
                $this->assertContainerBuilderNotHasService($loaderId);
                $this->assertContainerBuilderNotHasService($orchestratorId);
            }
        );
    }

    private function doAssertionsOnHttpClientServices(callable $asserter): void
    {
        $this->compile();

        foreach (self::HTTP_CLIENT_IDS as $httpClientId) {
            $purgerId = sprintf('kununu_testing.orchestrator.http_client.%s.purger', $httpClientId);
            $executorId = sprintf('kununu_testing.orchestrator.http_client.%s.executor', $httpClientId);
            $loaderId = sprintf('kununu_testing.orchestrator.http_client.%s.loader', $httpClientId);
            $orchestratorId = sprintf('kununu_testing.orchestrator.http_client.%s', $httpClientId);

            $asserter($purgerId, $executorId, $loaderId, $orchestratorId, $httpClientId);
        }
    }
}
