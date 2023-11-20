<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Test;

use Kununu\TestingBundle\Test\Options\Options;
use Symfony\Component\HttpFoundation\Response;

abstract class WebTestCase extends FixturesAwareTestCase
{
    final protected function doRequest(RequestBuilder $builder, string $httpClientName = 'http_client', ?Options $options = null): Response
    {
        $httpClientFixtures = $this->getHttpClientFixtures($httpClientName);

        if (!$options) {
            $options = Options::create();
        }

        $this->shutdown();

        $client = $this->getKernelBrowser();

        foreach ($httpClientFixtures as $fixtureClass => $fixture) {
            $this->loadHttpClientFixtures($httpClientName, $options, $fixtureClass);
        }

        $client->request(...$builder->build());

        $response = $client->getResponse();

        // Since there is no content, then there is also no content-type header.
        if (Response::HTTP_NO_CONTENT !== $response->getStatusCode()) {
            $this->assertTrue(
                $response->headers->contains(
                    'Content-type',
                    'application/json'
                )
            );
        }

        return $response;
    }
}
