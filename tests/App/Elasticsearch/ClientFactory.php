<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\App\Elasticsearch;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;

final class ClientFactory
{
    public function getInstance(array $hosts): Client
    {
        return ClientBuilder::create()
            ->setHosts($hosts)
            ->build();
    }
}
