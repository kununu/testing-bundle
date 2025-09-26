<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Test;

use Aws\DynamoDb\DynamoDbClient;
use Doctrine\DBAL\Connection;
use Elasticsearch\Client;
use OpenSearch\Client as OpenSearchClient;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

trait FixturesContainerGetterTrait
{
    protected function getConnection(string $connectionId = 'doctrine.dbal.default_connection'): Connection
    {
        $connection = $this->getServiceFromContainer($connectionId);
        assert($connection instanceof Connection);

        return $connection;
    }

    protected function getCachePool(string $cachePoolId): CacheItemPoolInterface
    {
        $cachePool = $this->getServiceFromContainer($cachePoolId);
        assert($cachePool instanceof CacheItemPoolInterface);

        return $cachePool;
    }

    protected function getElasticsearchClient(string $clientId = Client::class): Client
    {
        $client = $this->getServiceFromContainer($clientId);
        assert($client instanceof Client);

        return $client;
    }

    protected function getOpenSearchClient(string $clientId = OpenSearchClient::class): OpenSearchClient
    {
        $client = $this->getServiceFromContainer($clientId);
        assert($client instanceof OpenSearchClient);

        return $client;
    }

    protected function getHttpClient(string $httpClientId = 'http_client'): HttpClientInterface
    {
        $client = $this->getServiceFromContainer($httpClientId);
        assert($client instanceof HttpClientInterface);

        return $client;
    }

    protected function getDynamoDbClient(string $clientId = DynamoDbClient::class): DynamoDbClient
    {
        $client = $this->getServiceFromContainer($clientId);
        assert($client instanceof DynamoDbClient);

        return $client;
    }
}
