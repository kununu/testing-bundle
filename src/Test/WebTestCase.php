<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Test;

use Kununu\TestingBundle\Test\Options\Options;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class WebTestCase extends FixturesAwareTestCase
{
    final protected function doRequest(RequestBuilder $builder, string $httpClientName = 'http_client'): Response
    {
        $previousHttpFixtures = $this->getHttpClientFixturesClasses($httpClientName);

        $this->shutdown();

        $client = $this->getKernelBrowser();

        $this->restoreHttpClientFixtures($httpClientName, $previousHttpFixtures);

        $client->request(...$builder->build());

        $response = $client->getResponse();

        // Since there is no content, then there is also no content-type header.
        if (Response::HTTP_NO_CONTENT !== $response->getStatusCode()) {
            self::assertTrue($response->headers->contains('Content-type', 'application/json'));
        }

        return $response;
    }

    /** @codeCoverageIgnore */
    private function getHttpClientFixturesClasses(string $clientId): ?array
    {
        return match (interface_exists(HttpClientInterface::class)) {
            false => null,
            true  => array_filter(array_values(array_map(get_class(...), $this->getHttpClientFixtures($clientId)))),
        };
    }

    private function restoreHttpClientFixtures(string $clientId, ?array $fixtures): void
    {
        match (true) {
            is_array($fixtures) => $this->loadHttpClientFixtures(
                $clientId,
                Options::create()->withAppend()->withoutClear(),
                ...$fixtures
            ),
            default             => null,
        };
    }
}
