<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Command;

use Kununu\TestingBundle\Command\CopyConnectionSchemaCommand;
use Kununu\TestingBundle\Service\SchemaCopy\Factory\AdapterFactory;

/**
 * @group legacy
 */
final class CopyConnectionSchemaCommandTest extends AbstractCommandTestCase
{
    private const COMMAND = 'kununu_testing:connections:schema:copy';

    private const TABLES = [
        'doctrine_migration_versions',
        'table_1',
        'table_2',
        'table_3',
        'table_to_exclude',
    ];

    private const VIEWS = [
        'my_view',
    ];

    private $adapter;

    public function testCommandValidNonInteractive(): void
    {
        $this->executeCommand('monolithic', 'monolithic_test', '', false);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertEquals(self::TABLES, $this->adapter->getTables());
        $this->assertEquals(self::VIEWS, $this->adapter->getViews());
    }

    public function testCommandValidInteractiveConfirmed(): void
    {
        $this->executeCommand('monolithic', 'monolithic_test');

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertEquals(self::TABLES, $this->adapter->getTables());
        $this->assertEquals(self::VIEWS, $this->adapter->getViews());
    }

    public function testCommandValidInteractiveNotConfirmed(): void
    {
        $this->executeCommand('monolithic', 'monolithic_test', 'no');

        $this->assertEquals(1, $this->commandTester->getStatusCode());
        $this->assertEmpty($this->adapter->getTables());
        $this->assertEmpty($this->adapter->getViews());
    }

    public function testCommandInvalidNoArgs(): void
    {
        $this->executeCommand(null, null);

        $this->assertEquals(2, $this->commandTester->getStatusCode());
        $this->assertEmpty($this->adapter->getTables());
        $this->assertEmpty($this->adapter->getViews());
        $this->assertStringContainsString(
            '--from" argument can not be empty',
            $this->commandTester->getDisplay()
        );
    }

    public function testCommandInvalidFrom(): void
    {
        $this->executeCommand('i_do_not_exist', null);

        $this->assertEquals(2, $this->commandTester->getStatusCode());
        $this->assertEmpty($this->adapter->getTables());
        $this->assertEmpty($this->adapter->getViews());
        $this->assertStringContainsString(
            'Connection wanted to "--from" argument: "i_do_not_exist" was not found!',
            $this->commandTester->getDisplay()
        );
    }

    public function testCommandInvalidTo(): void
    {
        $this->executeCommand('monolithic', 'i_do_not_exist');

        $this->assertEquals(2, $this->commandTester->getStatusCode());
        $this->assertEmpty($this->adapter->getTables());
        $this->assertEmpty($this->adapter->getViews());
        $this->assertStringContainsString(
            'Connection wanted to "--to" argument: "i_do_not_exist" was not found!',
            $this->commandTester->getDisplay()
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $adapterFactory = new AdapterFactory();
        $this->adapter = $adapterFactory->createAdapter($this->getFixturesContainer()->get('doctrine.dbal.monolithic_test_connection'));
    }

    protected function getCommandClass(): string
    {
        return CopyConnectionSchemaCommand::class;
    }

    protected function getExistingCommandAlias(): string
    {
        return self::COMMAND;
    }

    protected function getNonExistingCommandAliases(): array
    {
        return [];
    }

    protected function preRunCommand(): void
    {
        $this->adapter->purgeTablesAndViews();
    }

    private function executeCommand(?string $from, ?string $to, string $confirmation = 'yes', bool $interactive = true): void
    {
        $args = [];

        if (null !== $from) {
            $args['-f'] = $from;
        }

        if (null !== $to) {
            $args['-t'] = $to;
        }

        $this->runCommand(self::COMMAND, $args, ['interactive' => $interactive], $interactive ? [$confirmation] : []);
    }
}
