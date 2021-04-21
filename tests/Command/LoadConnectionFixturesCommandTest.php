<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use Kununu\TestingBundle\Command\LoadConnectionFixturesCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @group integration
 */
final class LoadConnectionFixturesCommandTest extends KernelTestCase
{
    /** @var Connection */
    private $defaultConnection;
    /** @var Application */
    private $application;

    public function testExistsCommands(): void
    {
        $command = $this->application->find('kununu_testing:load_fixtures:connections:default');
        $this->assertInstanceOf(
            LoadConnectionFixturesCommand::class,
            $command,
            'Asserted that console command "kununu_testing:load_fixtures:connections:default" exists'
        );

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
        $this->prepareToRunCommand();

        $command = $this->application->find('kununu_testing:load_fixtures:connections:default');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'  => $command->getName(),
            '--append' => null,
        ]);

        $this->assertEquals(2, $this->defaultConnection->executeQuery('select count(1) from table_1')->fetchOne());
        $this->assertEquals(2, $this->defaultConnection->executeQuery('select count(1) from table_2')->fetchOne());
        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_1 where `name` = \'name0\'')->fetchOne());
        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_2 where `name` = \'name0\'')->fetchOne());
        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_1 where `name` = \'name3\'')->fetchOne());
        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_2 where `name` = \'name3\'')->fetchOne());
    }

    public function testExecuteNonAppendInteractive(): void
    {
        $this->prepareToRunCommand();

        $command = $this->application->find('kununu_testing:load_fixtures:connections:default');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['yes']);
        $commandTester->execute(
            [
                'command'  => $command->getName(),
            ]
        );

        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_1')->fetchOne());
        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_2')->fetchOne());
        $this->assertEquals(0, $this->defaultConnection->executeQuery('select count(1) from table_1 where `name` = \'name0\'')->fetchOne());
        $this->assertEquals(0, $this->defaultConnection->executeQuery('select count(1) from table_2 where `name` = \'name0\'')->fetchOne());
        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_1 where `name` = \'name3\'')->fetchOne());
        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_2 where `name` = \'name3\'')->fetchOne());
    }

    public function testExecuteNonAppendNonInteractive(): void
    {
        $this->prepareToRunCommand();

        $command = $this->application->find('kununu_testing:load_fixtures:connections:default');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            ['command'     => $command->getName()],
            ['interactive' => false]
        );

        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_1')->fetchOne());
        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_2')->fetchOne());
        $this->assertEquals(0, $this->defaultConnection->executeQuery('select count(1) from table_1 where `name` = \'name0\'')->fetchOne());
        $this->assertEquals(0, $this->defaultConnection->executeQuery('select count(1) from table_2 where `name` = \'name0\'')->fetchOne());
        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_1 where `name` = \'name3\'')->fetchOne());
        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_2 where `name` = \'name3\'')->fetchOne());
    }

    protected function setUp(): void
    {
        $kernel = static::bootKernel();
        $this->application = new Application($kernel);
        $this->defaultConnection = static::$container->get('doctrine.dbal.default_connection');
    }

    private function prepareToRunCommand(): void
    {
        $this->recreateConnectionDatabase();

        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_1')->fetchOne());
        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_2')->fetchOne());
        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_1 where `name` = \'name0\'')->fetchOne());
        $this->assertEquals(1, $this->defaultConnection->executeQuery('select count(1) from table_2 where `name` = \'name0\'')->fetchOne());
    }

    private function recreateConnectionDatabase(): void
    {
        $table1 = new Table('table_1', [
            new Column('name', Type::getType('string')),
            new Column('description', Type::getType('string')),
        ]);

        $table2 = new Table('table_2', [
            new Column('name', Type::getType('string')),
            new Column('description', Type::getType('string')),
        ]);

        $schemaManager = $this->defaultConnection->createSchemaManager();
        // Workaround with Sqlite since dropAndCreateDatabase is not working.
        // @todo Investigate further and open PR in doctrine repository to fix it
        $schemaManager->dropDatabase(static::$container->getParameter('doctrine_default_connection_path'));
        $schemaManager->createDatabase($this->defaultConnection->getDatabase());
        $schemaManager->createTable($table1);
        $schemaManager->createTable($table2);

        $this->defaultConnection->executeStatement('INSERT INTO `table_1` (`name`, `description`) VALUES (\'name0\', \'description0\');');
        $this->defaultConnection->executeStatement('INSERT INTO `table_2` (`name`, `description`) VALUES (\'name0\', \'description0\');');
    }
}
