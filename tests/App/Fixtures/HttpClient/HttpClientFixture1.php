<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\App\Fixtures\HttpClient;

use Kununu\DataFixtures\Adapter\DirectoryLoader\HttpClientArrayDirectoryFixture;
use Kununu\DataFixtures\InitializableFixtureInterface;

final class HttpClientFixture1 extends HttpClientArrayDirectoryFixture implements InitializableFixtureInterface
{
    public function initializeFixture(mixed ...$args): void
    {
    }
}
