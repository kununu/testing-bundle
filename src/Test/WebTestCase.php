<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Test;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

abstract class WebTestCase extends FixturesAwareTestCase
{
    /** @var KernelBrowser */
    private $client;

    final protected function doRequest(RequestBuilder $builder): Response
    {
        $this->initClient();

        $this->client->request(...$builder->build());

        /** @var Response $response */
        $response = $this->client->getResponse();

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

    private function initClient(): void
    {
        if (!$this->client) {
            $this->client = static::createClient();
        }
    }
}
