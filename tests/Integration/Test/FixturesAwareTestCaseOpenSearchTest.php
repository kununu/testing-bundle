<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Integration\Test;

use Kununu\TestingBundle\Test\FixturesAwareTestCase;
use Kununu\TestingBundle\Test\FixturesContainerGetterTrait;
use Kununu\TestingBundle\Test\Options\Options;
use Kununu\TestingBundle\Tests\App\Fixtures\OpenSearch\OpenSearchFixture1;
use Kununu\TestingBundle\Tests\App\Fixtures\OpenSearch\OpenSearchFixture2;
use OpenSearch\Client;
use OpenSearch\Common\Exceptions\Missing404Exception;

final class FixturesAwareTestCaseOpenSearchTest extends FixturesAwareTestCase
{
    use FixturesContainerGetterTrait;

    private Client $client;

    public function testLoadOpenSearchFixturesWithoutAppend(): void
    {
        $this->client->index(
            [
                'index' => 'my_index',
                'id'    => 'document_to_purge',
                'body'  => ['field' => 'value1'],
            ]
        );

        $this->registerInitializableFixtureForOpenSearch(
            'my_index_alias',
            OpenSearchFixture1::class,
            1,
            ['a' => 'name']
        );

        $this->loadOpenSearchFixtures(
            'my_index_alias',
            Options::create(),
            OpenSearchFixture1::class,
            OpenSearchFixture2::class
        );

        $purgedDocument = null;

        try {
            $purgedDocument = $this->client->get(['index' => 'my_index', 'id' => 'document_to_purge']);
        } catch (Missing404Exception) {
        }

        if (!empty($purgedDocument)) {
            $this->fail('Document should not exist!');
        }

        $document1 = $this->client->get(['index' => 'my_index', 'id' => 'my_id_1']);
        $document2 = $this->client->get(['index' => 'my_index', 'id' => 'my_id_2']);

        self::assertEquals('value_1', $document1['_source']['field']);
        self::assertEquals('value_2', $document2['_source']['field']);
    }

    public function testLoadOpenSearchFixturesWithAppend(): void
    {
        $this->client->index(
            [
                'index' => 'my_index',
                'id'    => 'document_to_not_purge',
                'body'  => ['field' => 'value1'],
            ]
        );

        $this->client->get(['index' => 'my_index', 'id' => 'document_to_not_purge']);

        $this->loadOpenSearchFixtures(
            'my_index_alias',
            Options::create()->withAppend(),
            OpenSearchFixture1::class,
            OpenSearchFixture2::class
        );

        $this->client->get(['index' => 'my_index', 'id' => 'document_to_not_purge']);

        $document1 = $this->client->get(['index' => 'my_index', 'id' => 'my_id_1']);
        $document2 = $this->client->get(['index' => 'my_index', 'id' => 'my_id_2']);

        self::assertEquals('value_1', $document1['_source']['field']);
        self::assertEquals('value_2', $document2['_source']['field']);
    }

    public function testClearFixtures(): void
    {
        $this->loadOpenSearchFixtures(
            'my_index_alias',
            Options::create(),
            OpenSearchFixture1::class,
            OpenSearchFixture2::class
        );
        $this->clearOpenSearchFixtures('my_index_alias');

        self::assertEmpty($this->getOpenSearchFixtures('my_index_alias'));
    }

    protected function setUp(): void
    {
        $this->client = $this->getOpenSearchClient();
    }
}
