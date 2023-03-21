<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Test;

use Symfony\Component\HttpFoundation\Response;

abstract class WebTestCase extends FixturesAwareTestCase
{
    final protected function doRequest(RequestBuilder $builder, bool $shutdown = true): Response
    {
        $client = $this->getKernelBrowser();

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

        if ($shutdown) {
            $this->shutdown();
        }

        return $response;
    }
}
