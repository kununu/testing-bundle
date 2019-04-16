<?php declare(strict_types=1);

namespace Kununu\TestingBundle\Test;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

abstract class WebTestCase extends FixturesAwareTestCase
{
    final protected function getClient() : Client
    {
        return static::createClient();
    }

    final protected function doRequest(Client $client, RequestBuilder $builder): Response
    {
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
}
