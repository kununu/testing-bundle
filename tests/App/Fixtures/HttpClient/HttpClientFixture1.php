<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\App\Fixtures\HttpClient;

use Kununu\DataFixtures\Adapter\HttpClientPhpArrayFixture;
use Kununu\DataFixtures\InitializableFixtureInterface;

final class HttpClientFixture1 extends HttpClientPhpArrayFixture implements InitializableFixtureInterface
{
    protected function fileNames(): array
    {
        return [
            __DIR__ . '/Http/fixture1.php',
        ];
    }

    public function initializeFixture(mixed ...$args): void
    {
    }
}
