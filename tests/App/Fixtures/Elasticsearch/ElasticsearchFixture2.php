<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\App\Fixtures\Elasticsearch;

use Elasticsearch\Client;
use Kununu\DataFixtures\Adapter\ElasticsearchFixtureInterface;

final readonly class ElasticsearchFixture2 implements ElasticsearchFixtureInterface
{
    public function load(Client $elasticSearch, string $indexName, bool $throwOnFail = true): void
    {
        $elasticSearch->index(
            [
                'index' => $indexName,
                'id'    => 'my_id_2',
                'body'  => ['field' => 'value_2'],
            ]
        );
    }
}
