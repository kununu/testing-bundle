<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\App\Fixtures\OpenSearch;

use Kununu\DataFixtures\Adapter\OpenSearchFixtureInterface;
use OpenSearch\Client;

final readonly class OpenSearchFixture2 implements OpenSearchFixtureInterface
{
    public function load(Client $client, string $indexName, bool $throwOnFail = true): void
    {
        $client->index(
            [
                'index' => $indexName,
                'id'    => 'my_id_2',
                'body'  => ['field' => 'value_2'],
            ]
        );
    }
}
