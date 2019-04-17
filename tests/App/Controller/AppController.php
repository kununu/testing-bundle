<?php declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

final class AppController
{
    public function response()
    {
        return new JsonResponse(['key' => 'value']);
    }
}
