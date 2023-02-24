<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Test;

use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Kununu\TestingBundle\Test\FixturesAwareTestCase;
use Kununu\TestingBundle\Test\Options\Options;
use Kununu\TestingBundle\Tests\App\Fixtures\ElasticSearch\ElasticSearchFixture1;
use Kununu\TestingBundle\Tests\App\Fixtures\ElasticSearch\ElasticSearchFixture2;

/** @group legacy */
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

        $this->registerInitializableFixtureForElasticSearch(
            'my_index_alias',
            ElasticSearchFixture1::class,
            1,
            ['a' => 'name']
        );

        $this->loadElasticSearchFixtures(
            'my_index_alias',
            Options::create(),
            ElasticSearchFixture1::class,
            ElasticSearchFixture2::class
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

        $this->loadElasticSearchFixtures(
            'my_index_alias',
            Options::create()->withAppend(),
            ElasticSearchFixture1::class,
            ElasticSearchFixture2::class
        );

        $this->client->get(['index' => 'my_index', 'id' => 'document_to_not_purge']);

        $document1 = $this->client->get(['index' => 'my_index', 'id' => 'my_id_1']);
        $document2 = $this->client->get(['index' => 'my_index', 'id' => 'my_id_2']);

        $this->assertEquals('value_1', $document1['_source']['field']);
        $this->assertEquals('value_2', $document2['_source']['field']);
    }

    public function testClearFixtures(): void
    {
        $this->loadElasticSearchFixtures(
            'my_index_alias',
            Options::create(),
            ElasticSearchFixture1::class,
            ElasticSearchFixture2::class
        );
        $this->clearElasticSearchFixtures('my_index_alias');
        $this->assertEmpty($this->getElasticSearchFixtures('my_index_alias'));
    }

    protected function setUp(): void
    {
        $this->client = $this->getFixturesContainer()->get('Kununu\TestingBundle\Tests\App\ElasticSearch');
    }
}
