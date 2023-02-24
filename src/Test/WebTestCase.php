<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Test;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

abstract class WebTestCase extends FixturesAwareTestCase
{
    private ?KernelBrowser $client = null;

    final protected function doRequest(RequestBuilder $builder, bool $shutdown = true): Response
    {
        $this->initClient();

        $this->client->request(...$builder->build());

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

        if ($shutdown) {
            $this->ensureKernelShutdown();
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
