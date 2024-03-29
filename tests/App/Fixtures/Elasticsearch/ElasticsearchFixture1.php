<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\App\Fixtures\Elasticsearch;

use Elasticsearch\Client;
use Kununu\DataFixtures\Adapter\ElasticsearchFixtureInterface;
use Kununu\DataFixtures\InitializableFixtureInterface;

final class ElasticsearchFixture1 implements ElasticsearchFixtureInterface, InitializableFixtureInterface
{
    private ?int $arg1 = null;
    private ?array $arg2 = null;

    public function load(Client $elasticSearch, string $indexName, bool $throwOnFail = true): void
    {
        $elasticSearch->index(
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
