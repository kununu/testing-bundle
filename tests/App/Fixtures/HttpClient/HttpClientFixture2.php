<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\App\Fixtures\HttpClient;

use Kununu\DataFixtures\Adapter\HttpClientPhpArrayFixture;

final class HttpClientFixture2 extends HttpClientPhpArrayFixture
{
    protected function fileNames(): array
    {
        return [
            __DIR__ . '/Http/fixture2.php',
        ];
    }
}
