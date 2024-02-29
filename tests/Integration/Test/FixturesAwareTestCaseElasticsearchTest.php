<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Integration\Test;

use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Kununu\TestingBundle\Test\FixturesAwareTestCase;
use Kununu\TestingBundle\Test\Options\Options;
use Kununu\TestingBundle\Tests\App\Fixtures\Elasticsearch\ElasticsearchFixture1;
use Kununu\TestingBundle\Tests\App\Fixtures\Elasticsearch\ElasticsearchFixture2;

final class FixturesAwareTestCaseElasticsearchTest extends FixturesAwareTestCase
{
    private Client $client;

    public function testLoadElasticSearchFixturesWithoutAppend(): void
    {
        $this->client->index(
            [
                'index' => 'my_index',
                'id'    => 'document_to_purge',
                'body'  => ['field' => 'value1'],
            ]
        );

        $this->registerInitializableFixtureForElasticsearch(
            'my_index_alias',
            ElasticsearchFixture1::class,
            1,
            ['a' => 'name']
        );

        $this->loadElasticsearchFixtures(
            'my_index_alias',
            Options::create(),
            ElasticsearchFixture1::class,
            ElasticsearchFixture2::class
        );

        $purgedDocument = null;

        try {
            $purgedDocument = $this->client->get(['index' => 'my_index', 'id' => 'document_to_purge']);
        } catch (Missing404Exception $missing404Exception) {
        }

        if (!empty($purgedDocument)) {
            $this->fail('Document should not exist!');
        }

        $document1 = $this->client->get(['index' => 'my_index', 'id' => 'my_id_1']);
        $document2 = $this->client->get(['index' => 'my_index', 'id' => 'my_id_2']);

        $this->assertEquals('value_1', $document1['_source']['field']);
        $this->assertEquals('value_2', $document2['_source']['field']);
    }

    public function testLoadElasticSearchFixturesWithAppend(): void
    {
        $this->client->index(
            [
                'index' => 'my_index',
                'id'    => 'document_to_not_purge',
                'body'  => ['field' => 'value1'],
            ]
        );

        $this->client->get(['index' => 'my_index', 'id' => 'document_to_not_purge']);

        $this->loadElasticsearchFixtures(
            'my_index_alias',
            Options::create()->withAppend(),
            ElasticsearchFixture1::class,
            ElasticsearchFixture2::class
        );

        $this->client->get(['index' => 'my_index', 'id' => 'document_to_not_purge']);

        $document1 = $this->client->get(['index' => 'my_index', 'id' => 'my_id_1']);
        $document2 = $this->client->get(['index' => 'my_index', 'id' => 'my_id_2']);

        $this->assertEquals('value_1', $document1['_source']['field']);
        $this->assertEquals('value_2', $document2['_source']['field']);
    }

    public function testClearFixtures(): void
    {
        $this->loadElasticsearchFixtures(
            'my_index_alias',
            Options::create(),
            ElasticsearchFixture1::class,
            ElasticsearchFixture2::class
        );
        $this->clearElasticsearchFixtures('my_index_alias');
        $this->assertEmpty($this->getElasticsearchFixtures('my_index_alias'));
    }

    protected function setUp(): void
    {
        $this->client = $this->getFixturesContainer()->get(Client::class);
    }
}
