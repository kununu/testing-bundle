<?php declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\App\Fixtures\ElasticSearch;

use Elasticsearch\Client;
use Kununu\DataFixtures\Adapter\ElasticSearchFixtureInterface;

final class ElasticSearchFixture2 implements ElasticSearchFixtureInterface
{
    public function load(Client $elasticSearch): void
    {
        $elasticSearch->index(
            [
                'index' => 'my_index',
                'id'    => 'my_id_2',
                'body'  => ['field' => 'value_2']
            ]
        );
    }
}
