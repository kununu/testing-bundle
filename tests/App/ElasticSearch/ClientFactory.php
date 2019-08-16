<?php declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\App\ElasticSearch;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;

final class ClientFactory
{
    public static function getInstance(array $hosts): Client
    {
        return ClientBuilder::create()
            ->setHosts($hosts)
            ->build();
    }
}
