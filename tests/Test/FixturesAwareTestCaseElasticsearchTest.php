<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Test;

use Elasticsearch\Client as ElasticSearch;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Kununu\TestingBundle\Test\FixturesAwareTestCase;
use Kununu\TestingBundle\Tests\App\Fixtures\ElasticSearch\ElasticSearchFixture1;
use Kununu\TestingBundle\Tests\App\Fixtures\ElasticSearch\ElasticSearchFixture2;

/**
 * @group legacy
 */
final class FixturesAwareTestCaseElasticsearchTest extends FixturesAwareTestCase
{
    /** @var ElasticSearch */
    private $elasticSearch;

    public function testLoadElasticSearchFixturesWithoutAppend(): void
    {
        $this->elasticSearch->index(
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
            [ElasticSearchFixture1::class, ElasticSearchFixture2::class]
        );

        $purgedDocument = null;

        try {
            $purgedDocument = $this->elasticSearch->get(['index' => 'my_index', 'id' => 'document_to_purge']);
        } catch (Missing404Exception $missing404Exception) {
        }

        if (!empty($purgedDocument)) {
            $this->fail('Document should not exist!');
        }

        $document1 = $this->elasticSearch->get(['index' => 'my_index', 'id' => 'my_id_1']);
        $document2 = $this->elasticSearch->get(['index' => 'my_index', 'id' => 'my_id_2']);

        $this->assertEquals('value_1', $document1['_source']['field']);
        $this->assertEquals('value_2', $document2['_source']['field']);
    }

    public function testLoadElasticSearchFixturesWithAppend(): void
    {
        $this->elasticSearch->index(
            [
                'index' => 'my_index',
                'id'    => 'document_to_not_purge',
                'body'  => ['field' => 'value1'],
            ]
        );

        $this->elasticSearch->get(['index' => 'my_index', 'id' => 'document_to_not_purge']);

        $this->loadElasticSearchFixtures(
            'my_index_alias',
            [ElasticSearchFixture1::class, ElasticSearchFixture2::class],
            true
        );

        $this->elasticSearch->get(['index' => 'my_index', 'id' => 'document_to_not_purge']);

        $document1 = $this->elasticSearch->get(['index' => 'my_index', 'id' => 'my_id_1']);
        $document2 = $this->elasticSearch->get(['index' => 'my_index', 'id' => 'my_id_2']);

        $this->assertEquals('value_1', $document1['_source']['field']);
        $this->assertEquals('value_2', $document2['_source']['field']);
    }

    public function testClearFixtures(): void
    {
        $this->loadElasticSearchFixtures('my_index_alias', [ElasticSearchFixture1::class, ElasticSearchFixture2::class]);
        $this->clearElasticSearchFixtures('my_index_alias');
        $this->assertEmpty($this->getElasticSearchFixtures('my_index_alias'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->elasticSearch = $this->getFixturesContainer()->get('Kununu\TestingBundle\Tests\App\ElasticSearch');
    }
}