<?php declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\App\Fixtures\ElasticSearch;

use Elasticsearch\Client;
use Kununu\DataFixtures\Adapter\ElasticSearchFixtureInterface;

final class ElasticSearchFixture1 implements ElasticSearchFixtureInterface
{
    public function load(Client $elasticSearch): void
    {
        $elasticSearch->index(
            [
                'index' => 'my_index',
                'id'    => 'my_id_1',
                'body'  => ['field' => 'value_1']
            ]
        );
    }
}
