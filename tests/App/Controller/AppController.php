<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route(
    path: 'response/{id}',
    name: 'app.response',
    requirements: ['id' => '\d+'],
    defaults: ['id' => null],
    methods: [Request::METHOD_GET],
)]
final readonly class AppController
{
    public function __construct(private HttpClientInterface $client)
    {
    }

    public function __invoke(?int $id): JsonResponse
    {
        if ($id) {
            $response = $this->client->request('GET', sprintf('https://my-external-service.fake/external/%d', $id));
            $value = json_decode($response->getContent(), true)['value'];
        } else {
            $value = 'value';
        }

        return new JsonResponse(['key' => $value]);
    }
}
