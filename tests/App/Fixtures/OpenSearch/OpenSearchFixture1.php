<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\App\Fixtures\OpenSearch;

use Kununu\DataFixtures\Adapter\OpenSearchFixtureInterface;
use Kununu\DataFixtures\InitializableFixtureInterface;
use OpenSearch\Client;

final class OpenSearchFixture1 implements OpenSearchFixtureInterface, InitializableFixtureInterface
{
    private ?int $arg1 = null;
    private ?array $arg2 = null;

    public function load(Client $client, string $indexName, bool $throwOnFail = true): void
    {
        $client->index(
            [
                'index' => $indexName,
                'id'    => 'my_id_1',
                'body'  => ['field' => 'value_1'],
            ]
        );
    }

    public function initializeFixture(mixed ...$args): void
    {
        foreach ($args as $index => $arg) {
            if (0 === $index && is_int($arg)) {
                $this->arg1 = $arg;
            }

            if (1 === $index && is_array($arg)) {
                $this->arg2 = $arg;
            }
        }
    }

    public function arg1(): ?int
    {
        return $this->arg1;
    }

    public function arg2(): ?array
    {
        return $this->arg2;
    }
}
