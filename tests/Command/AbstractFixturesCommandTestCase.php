<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Command;

use Kununu\TestingBundle\Test\FixturesAwareTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Tester\CommandTester;

abstract class AbstractFixturesCommandTestCase extends FixturesAwareTestCase
{
    /** @var Application */
    protected $application;

    public function testExistsCommands(): void
    {
        $existingCommandAlias = $this->getExistingCommandAlias();

        $command = $this->application->find($existingCommandAlias);

        $this->assertInstanceOf(
            $this->getCommandClass(),
            $command,
            sprintf('Asserted that console command "%s" exists', $existingCommandAlias)
        );

        foreach ($this->getNonExistingCommandAliases() as $nonExistingCommandAlias) {
            try {
                $this->application->find($nonExistingCommandAlias);
                $this->fail(sprintf('Console command "%s" should not exist', $nonExistingCommandAlias));
            } catch (CommandNotFoundException $exception) {
                $this->assertTrue(
                    true,
                    sprintf('Asserted that console command "%s" does not exist', $nonExistingCommandAlias)
                );
            }
        }
    }

    public function testExecuteAppend(): void
    {
        $this->runCommand($this->getExistingCommandAlias(), ['--append' => null]);

        $this->doAssertionsForExecuteAppend();
    }

    public function testExecuteNonAppendInteractive(): void
    {
        $this->runCommand($this->getExistingCommandAlias(), [], [], ['yes']);

        $this->doAssertionsForExecuteNonAppendInteractive();
    }

    public function testExecuteNonAppendNonInteractive(): void
    {
        $this->runCommand($this->getExistingCommandAlias(), [], ['interactive' => false], ['yes']);

        $this->doAssertionsForExecuteNonAppendNonInteractive();
    }

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->application = new Application($kernel);
    }

    protected function runCommand(string $commandAlias, array $args, array $options = [], array $inputs = []): void
    {
        $this->preRunCommand();

        $command = $this->application->find($commandAlias);
        $commandTester = new CommandTester($command);
        $commandTester->setInputs($inputs);
        $commandTester->execute(
            array_merge(['command' => $command->getName()], $args),
            $options
        );
    }

    abstract protected function getCommandClass(): string;

    abstract protected function doAssertionsForExecuteAppend(): void;

    abstract protected function doAssertionsForExecuteNonAppendInteractive(): void;

    abstract protected function doAssertionsForExecuteNonAppendNonInteractive(): void;

    abstract protected function getExistingCommandAlias(): string;

    abstract protected function getNonExistingCommandAliases(): array;

    abstract protected function preRunCommand(): void;
}
