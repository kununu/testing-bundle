<?php declare(strict_types=1);

namespace Kununu\TestingBundle\Test;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

abstract class WebTestCase extends FixturesAwareTestCase
{
    final protected function doRequest(RequestBuilder $builder): Response
    {
        $this->initClient();

        /** @var KernelBrowser $client */
        $client = static::$client;

        $client->request(...$builder->build());

        $response = $client->getResponse();

        // Since there is no content, then there is also no content-type header.
        if ($response->getStatusCode() !== Response::HTTP_NO_CONTENT) {
            $this->assertTrue(
                $response->headers->contains(
                    'Content-type',
                    'application/json'
                )
            );
        }

        return $response;
    }

    private function initClient() : void
    {
        if (!static::$client) {
            static::createClient();
        }
    }
}
