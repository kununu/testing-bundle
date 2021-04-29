<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Command;

use Doctrine\DBAL\Connection;
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
    private $defConnection;
    /** @var Application */
    private $application;

    public function testExistsCommands(): void
    {
        $command = $this->application->find('kununu_testing:load_fixtures:connections:def');
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

        $command = $this->application->find('kununu_testing:load_fixtures:connections:def');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'  => $command->getName(),
            '--append' => null,
        ]);

        $this->assertEquals(2, $this->defConnection->executeQuery('select count(1) from table_1')->fetchOne());
        $this->assertEquals(2, $this->defConnection->executeQuery('select count(1) from table_2')->fetchOne());
        $this->assertEquals(1, $this->defConnection->executeQuery('select count(1) from table_1 where `name` = \'name0\'')->fetchOne());
        $this->assertEquals(1, $this->defConnection->executeQuery('select count(1) from table_2 where `name` = \'name0\'')->fetchOne());
        $this->assertEquals(1, $this->defConnection->executeQuery('select count(1) from table_1 where `name` = \'name3\'')->fetchOne());
        $this->assertEquals(1, $this->defConnection->executeQuery('select count(1) from table_2 where `name` = \'name3\'')->fetchOne());
    }

    public function testExecuteNonAppendInteractive(): void
    {
        $this->prepareToRunCommand();

        $command = $this->application->find('kununu_testing:load_fixtures:connections:def');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['yes']);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $this->assertEquals(1, $this->defConnection->executeQuery('select count(1) from table_1')->fetchOne());
        $this->assertEquals(1, $this->defConnection->executeQuery('select count(1) from table_2')->fetchOne());
        $this->assertEquals(0, $this->defConnection->executeQuery('select count(1) from table_1 where `name` = \'name0\'')->fetchOne());
        $this->assertEquals(0, $this->defConnection->executeQuery('select count(1) from table_2 where `name` = \'name0\'')->fetchOne());
        $this->assertEquals(1, $this->defConnection->executeQuery('select count(1) from table_1 where `name` = \'name3\'')->fetchOne());
        $this->assertEquals(1, $this->defConnection->executeQuery('select count(1) from table_2 where `name` = \'name3\'')->fetchOne());
    }

    public function testExecuteNonAppendNonInteractive(): void
    {
        $this->prepareToRunCommand();

        $command = $this->application->find('kununu_testing:load_fixtures:connections:def');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            ['command' => $command->getName()],
            ['interactive' => false]
        );

        $this->assertEquals(1, $this->defConnection->executeQuery('select count(1) from table_1')->fetchOne());
        $this->assertEquals(1, $this->defConnection->executeQuery('select count(1) from table_2')->fetchOne());
        $this->assertEquals(0, $this->defConnection->executeQuery('select count(1) from table_1 where `name` = \'name0\'')->fetchOne());
        $this->assertEquals(0, $this->defConnection->executeQuery('select count(1) from table_2 where `name` = \'name0\'')->fetchOne());
        $this->assertEquals(1, $this->defConnection->executeQuery('select count(1) from table_1 where `name` = \'name3\'')->fetchOne());
        $this->assertEquals(1, $this->defConnection->executeQuery('select count(1) from table_2 where `name` = \'name3\'')->fetchOne());
    }

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->application = new Application($kernel);
        $this->defConnection = self::$container->get('doctrine.dbal.def_connection');
    }

    private function prepareToRunCommand(): void
    {
        $this->defConnection->executeStatement('TRUNCATE `table_1`');
        $this->defConnection->executeStatement('TRUNCATE `table_2`');
        $this->defConnection->executeStatement('INSERT INTO `table_1` (`name`, `description`) VALUES (\'name0\', \'description0\');');
        $this->defConnection->executeStatement('INSERT INTO `table_2` (`name`, `description`) VALUES (\'name0\', \'description0\');');

        $this->assertEquals(1, $this->defConnection->executeQuery('select count(1) from table_1')->fetchOne());
        $this->assertEquals(1, $this->defConnection->executeQuery('select count(1) from table_2')->fetchOne());
        $this->assertEquals(1, $this->defConnection->executeQuery('select count(1) from table_1 where `name` = \'name0\'')->fetchOne());
        $this->assertEquals(1, $this->defConnection->executeQuery('select count(1) from table_2 where `name` = \'name0\'')->fetchOne());
    }
}
