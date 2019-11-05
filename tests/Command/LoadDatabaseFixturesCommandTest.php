<?php declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use Kununu\TestingBundle\Command\LoadDatabaseFixturesCommand;
use Kununu\TestingBundle\Test\FixturesAwareTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @group integration
 */
final class LoadDatabaseFixturesCommandTest extends FixturesAwareTestCase
{
    /** @var Connection */
    private $defaultConnection;
    /** @var Application */
    private $application;

    public function testExistsCommands(): void
    {
        $command = $this->application->find('kununu_testing:load_fixtures:connections:default');
        $this->assertInstanceOf(LoadDatabaseFixturesCommand::class, $command, 'Asserted that console command "kununu_testing:load_fixtures:connections:default" exists');

        try {
            $this->application->find('kununu_testing:load_fixtures:connections:persistence');
            $this->fail('Console command "kununu_testing:load_fixtures:connections:persistence" should not exist');
        } catch (CommandNotFoundException $exception) {
            $this->assertTrue(true, 'Asserted that console command "kununu_testing:load_fixtures:connections:persistence" does not exist');
        }

        try {
            $this->application->find('kununu_testing:load_fixtures:connections:monolithic');
            $this->fail('Console command "kununu_testing:load_fixtures:connections:monolithic" should not exist');
        } catch (CommandNotFoundException $exception) {
            $this->assertTrue(true, 'Asserted that console command "kununu_testing:load_fixtures:connections:monolithic" does not exist');
        }
    }

    public function testExecuteAppend(): void
    {
        $this->recreateConnectionDatabase($this->defaultConnection);

        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_1')->fetchColumn());
        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_2')->fetchColumn());
        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_1 where `name` = \'name0\'')->fetchColumn());
        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_2 where `name` = \'name0\'')->fetchColumn());

        $command = $this->application->find('kununu_testing:load_fixtures:connections:default');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'  => $command->getName(),
            '--append' => null
        ]);

        $this->assertEquals(2, $this->defaultConnection->executeQuery('select count(1) from table_1')->fetchColumn());
        $this->assertEquals(2, $this->defaultConnection->executeQuery('select count(1) from table_2')->fetchColumn());
        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_1 where `name` = \'name0\'')->fetchColumn());
        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_2 where `name` = \'name0\'')->fetchColumn());
        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_1 where `name` = \'name3\'')->fetchColumn());
        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_2 where `name` = \'name3\'')->fetchColumn());
    }

    public function testExecuteNonAppendInteractive(): void
    {
        $this->recreateConnectionDatabase($this->defaultConnection);

        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_1')->fetchColumn());
        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_2')->fetchColumn());
        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_1 where `name` = \'name0\'')->fetchColumn());
        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_2 where `name` = \'name0\'')->fetchColumn());

        $command = $this->application->find('kununu_testing:load_fixtures:connections:default');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['yes']);
        $commandTester->execute(
            [
                'command'  => $command->getName(),
            ]
        );

        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_1')->fetchColumn());
        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_2')->fetchColumn());
        $this->assertEquals(0, $this->defaultConnection->executeQuery('select count(1) from table_1 where `name` = \'name0\'')->fetchColumn());
        $this->assertEquals(0, $this->defaultConnection->executeQuery('select count(1) from table_2 where `name` = \'name0\'')->fetchColumn());
        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_1 where `name` = \'name3\'')->fetchColumn());
        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_2 where `name` = \'name3\'')->fetchColumn());
    }

    public function testExecuteNonAppendNonInteractive(): void
    {
        $this->recreateConnectionDatabase($this->defaultConnection);

        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_1')->fetchColumn());
        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_2')->fetchColumn());
        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_1 where `name` = \'name0\'')->fetchColumn());
        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_2 where `name` = \'name0\'')->fetchColumn());

        $command = $this->application->find('kununu_testing:load_fixtures:connections:default');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            ['command'     => $command->getName()],
            ['interactive' => false]
        );

        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_1')->fetchColumn());
        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_2')->fetchColumn());
        $this->assertEquals(0, $this->defaultConnection->executeQuery('select count(1) from table_1 where `name` = \'name0\'')->fetchColumn());
        $this->assertEquals(0, $this->defaultConnection->executeQuery('select count(1) from table_2 where `name` = \'name0\'')->fetchColumn());
        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_1 where `name` = \'name3\'')->fetchColumn());
        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_2 where `name` = \'name3\'')->fetchColumn());
    }

    protected function setUp(): void
    {
        static::bootKernel();

        $this->defaultConnection = static::$container->get('doctrine.dbal.default_connection');
        $this->application       = new Application(static::$kernel);
    }

    private function recreateConnectionDatabase(Connection $connection): void
    {
        $table1 = new Table('table_1', [
            new Column('name', Type::getType('string')),
            new Column('description', Type::getType('string')),
        ]);

        $table2 = new Table('table_2', [
            new Column('name', Type::getType('string')),
            new Column('description', Type::getType('string')),
        ]);

        $schemaManager = $connection->getSchemaManager();
        $schemaManager->dropAndCreateDatabase($connection->getDatabase());
        $schemaManager->createTable($table1);
        $schemaManager->createTable($table2);

        $connection->exec('INSERT INTO `table_1` (`name`, `description`) VALUES (\'name0\', \'description0\');');
        $connection->exec('INSERT INTO `table_2` (`name`, `description`) VALUES (\'name0\', \'description0\');');
    }
}
