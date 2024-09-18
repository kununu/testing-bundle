<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Integration\Command;

use Kununu\TestingBundle\Command\CopyConnectionSchemaCommand;
use Kununu\TestingBundle\Service\SchemaCopy\SchemaCopyAdapterFactoryInterface;
use Kununu\TestingBundle\Service\SchemaCopy\SchemaCopyAdapterInterface;

final class CopyConnectionSchemaCommandTest extends AbstractCommandTestCase
{
    private const string COMMAND = 'kununu_testing:connections:schema:copy';

    private const array TABLES = [
        'doctrine_migration_versions',
        'table_1',
        'table_2',
        'table_3',
        'table_to_exclude',
    ];

    private const array VIEWS = [
        'my_view',
    ];

    private SchemaCopyAdapterInterface $adapter;

    public function testCommandValidNonInteractive(): void
    {
        $this->executeCommand('monolithic', 'monolithic_test', '', false);

        self::assertEquals(0, $this->commandTester->getStatusCode());
        self::assertEquals(self::TABLES, $this->adapter->getTables());
        self::assertEquals(self::VIEWS, $this->adapter->getViews());
    }

    public function testCommandValidInteractiveConfirmed(): void
    {
        $this->executeCommand('monolithic', 'monolithic_test');

        self::assertEquals(0, $this->commandTester->getStatusCode());
        self::assertEquals(self::TABLES, $this->adapter->getTables());
        self::assertEquals(self::VIEWS, $this->adapter->getViews());
    }

    public function testCommandValidInteractiveNotConfirmed(): void
    {
        $this->executeCommand('monolithic', 'monolithic_test', 'no');

        self::assertEquals(1, $this->commandTester->getStatusCode());
        self::assertEmpty($this->adapter->getTables());
        self::assertEmpty($this->adapter->getViews());
    }

    public function testCommandInvalidNoArgs(): void
    {
        $this->executeCommand(null, null);

        self::assertEquals(2, $this->commandTester->getStatusCode());
        self::assertEmpty($this->adapter->getTables());
        self::assertEmpty($this->adapter->getViews());
        self::assertStringContainsString(
            '--from" argument can not be empty',
            $this->commandTester->getDisplay()
        );
    }

    public function testCommandInvalidFrom(): void
    {
        $this->executeCommand('i_do_not_exist', null);

        self::assertEquals(2, $this->commandTester->getStatusCode());
        self::assertEmpty($this->adapter->getTables());
        self::assertEmpty($this->adapter->getViews());
        self::assertStringContainsString(
            'Connection wanted to "--from" argument: "i_do_not_exist" was not found!',
            $this->commandTester->getDisplay()
        );
    }

    public function testCommandInvalidTo(): void
    {
        $this->executeCommand('monolithic', 'i_do_not_exist');

        self::assertEquals(2, $this->commandTester->getStatusCode());
        self::assertEmpty($this->adapter->getTables());
        self::assertEmpty($this->adapter->getViews());
        self::assertStringContainsString(
            'Connection wanted to "--to" argument: "i_do_not_exist" was not found!',
            $this->commandTester->getDisplay()
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        /** @var SchemaCopyAdapterFactoryInterface $adapterFactory */
        $adapterFactory = $this->getServiceFromContainer('kununu_testing.schema_copy_adapter_factory');

        $this->adapter = $adapterFactory->createAdapter(
            $this->getConnection('doctrine.dbal.monolithic_test_connection')
        );
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

    private function executeCommand(
        ?string $from,
        ?string $to,
        string $confirmation = 'yes',
        bool $interactive = true,
    ): void {
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
