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
        $previousHttpClientFixtures = $this->getPreviousHttpClientFixtures($httpClientName);

        $this->shutdown();

        $client = $this->getKernelBrowser();

        $this->restorePreviousHttpClientFixtures($httpClientName, $previousHttpClientFixtures);

        $client->request(...$builder->build());

        $response = $client->getResponse();

        // Since there is no content, then there is also no content-type header.
        if (Response::HTTP_NO_CONTENT !== $response->getStatusCode()) {
            self::assertTrue($response->headers->contains('Content-type', 'application/json'));
        }

        return $response;
    }

    /** @codeCoverageIgnore */
    private function getPreviousHttpClientFixtures(string $httpClientName): ?array
    {
        return interface_exists(HttpClientInterface::class) ? $this->getHttpClientFixtures($httpClientName) : null;
    }

    private function restorePreviousHttpClientFixtures(string $httpClientName, ?array $httpClientFixtures): void
    {
        foreach ($httpClientFixtures ?? [] as $fixtureClass => $fixture) {
            $this->loadHttpClientFixtures(
                $httpClientName,
                Options::create()->withoutClear()->withAppend(),
                $fixtureClass
            );
        }
    }
}
