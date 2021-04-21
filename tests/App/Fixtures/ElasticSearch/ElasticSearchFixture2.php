<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\App\Fixtures\ElasticSearch;

use Elasticsearch\Client;
use Kununu\DataFixtures\Adapter\ElasticsearchFixtureInterface;

final class ElasticSearchFixture2 implements ElasticSearchFixtureInterface
{
    public function load(Client $elasticSearch, string $indexName): void
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
