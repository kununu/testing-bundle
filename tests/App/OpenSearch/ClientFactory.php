<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\App\OpenSearch;

use OpenSearch\Client;
use OpenSearch\ClientBuilder;

final class ClientFactory
{
    public function getInstance(array $hosts): Client
    {
        return ClientBuilder::create()
            ->setHosts($hosts)
            ->build();
    }
}
