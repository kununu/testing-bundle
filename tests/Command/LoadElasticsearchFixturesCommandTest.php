<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Command;

use Elasticsearch\Client;
use Kununu\TestingBundle\Command\LoadElasticsearchFixturesCommand;
use Kununu\TestingBundle\Test\FixturesAwareTestCase;
use Kununu\TestingBundle\Tests\App\Fixtures\ElasticSearch\ElasticSearchFixture1;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Tester\CommandTester;

final class LoadElasticsearchFixturesCommandTest extends FixturesAwareTestCase
{
    /** @var Client */
    private $elasticsearchClient;
    /** @var Application */
    private $application;

    public function testExistsCommands(): void
    {
        $command = $this->application->find('kununu_testing:load_fixtures:elastic_search:my_index_alias');
        $this->assertInstanceOf(
            LoadElasticsearchFixturesCommand::class,
            $command,
            'Asserted that console command "kununu_testing:load_fixtures:elastic_search:my_index_alias" exists'
        );

        try {
            $this->application->find('kununu_testing:load_fixtures:elastic_search:my_index_alias_2');
            $this->fail('Console command "kununu_testing:load_fixtures:elastic_search:my_index_alias_2" should not exist');
        } catch (CommandNotFoundException $exception) {
            $this->assertTrue(true,
                'Asserted that console command "kununu_testing:load_fixtures:elastic_search:my_index_alias_2" does not exist');
        }
    }

    public function testExecuteAppend(): void
    {
        $this->prepareToRunCommand();

        $command = $this->application->find('kununu_testing:load_fixtures:elastic_search:my_index_alias');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'  => $command->getName(),
            '--append' => null,
        ]);

        $this->assertEquals(2, $this->countDocumentsInIndex());
    }

    public function testExecuteNonAppendInteractive(): void
    {
        $this->prepareToRunCommand();

        $command = $this->application->find('kununu_testing:load_fixtures:elastic_search:my_index_alias');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['yes']);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $this->assertEquals(1, $this->countDocumentsInIndex());
    }

    public function testExecuteNonAppendNonInteractive(): void
    {
        $this->prepareToRunCommand();

        $command = $this->application->find('kununu_testing:load_fixtures:elastic_search:my_index_alias');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            ['command' => $command->getName()],
            ['interactive' => false]
        );

        $this->assertEquals(1, $this->countDocumentsInIndex());
    }

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->application = new Application($kernel);
        $this->elasticsearchClient = $this->getFixturesContainer()->get('Kununu\TestingBundle\Tests\App\ElasticSearch');
    }

    private function prepareToRunCommand(): void
    {
        $this->loadElasticSearchFixtures('my_index_alias', [ElasticSearchFixture1::class]);

        $this->assertEquals(1, $this->countDocumentsInIndex());
    }

    private function countDocumentsInIndex(): int
    {
        return $this->elasticsearchClient->count(['index' => 'my_index'])['count'];
    }
}
