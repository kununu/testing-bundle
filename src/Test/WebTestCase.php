<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Test;

use Kununu\TestingBundle\Test\Options\Options;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Response;

abstract class WebTestCase extends FixturesAwareTestCase
{
    final protected function doRequest(RequestBuilder $builder, string $httpClientName = 'http_client', ?Options $options = null): Response
    {
        try {
            $httpClientFixtures = $this->getHttpClientFixtures($httpClientName);
        } catch (ServiceNotFoundException) {
            $httpClientFixtures = null;
        }

        $this->shutdown();

        $client = $this->getKernelBrowser();

        foreach ($httpClientFixtures ?? [] as $fixtureClass => $fixture) {
            $this->loadHttpClientFixtures($httpClientName, $options ?? Options::create(), $fixtureClass);
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
