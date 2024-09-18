<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Integration\Command;

use Elasticsearch\Client;
use Kununu\TestingBundle\Command\LoadElasticsearchFixturesCommand;
use Kununu\TestingBundle\Test\Options\Options;
use Kununu\TestingBundle\Tests\App\Fixtures\Elasticsearch\ElasticsearchFixture1;

final class LoadElasticsearchFixturesCommandTest extends AbstractFixturesCommandTestCase
{
    private const string COMMAND_1 = 'kununu_testing:load_fixtures:elastic_search:my_index_alias';
    private const string COMMAND_2 = 'kununu_testing:load_fixtures:elastic_search:my_index_alias_2';

    private Client $client;

    protected function doAssertionsForExecuteAppend(): void
    {
        self::assertEquals(2, $this->countDocumentsInIndex());
    }

    protected function doAssertionsForExecuteNonAppendInteractive(): void
    {
        self::assertEquals(1, $this->countDocumentsInIndex());
    }

    protected function doAssertionsForExecuteNonAppendNonInteractive(): void
    {
        self::assertEquals(1, $this->countDocumentsInIndex());
    }

    protected function getCommandClass(): string
    {
        return LoadElasticsearchFixturesCommand::class;
    }

    protected function getExistingCommandAlias(): string
    {
        return self::COMMAND_1;
    }

    protected function getNonExistingCommandAliases(): array
    {
        return [self::COMMAND_2];
    }

    protected function preRunCommand(): void
    {
        $this->loadElasticsearchFixtures('my_index_alias', Options::create(), ElasticsearchFixture1::class);

        self::assertEquals(1, $this->countDocumentsInIndex());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->getElasticsearchClient();
    }

    private function countDocumentsInIndex(): int
    {
        return $this->client->count(['index' => 'my_index'])['count'];
    }
}
