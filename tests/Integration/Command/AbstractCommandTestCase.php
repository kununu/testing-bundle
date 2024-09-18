<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Integration\Command;

use Kununu\TestingBundle\Test\FixturesAwareTestCase;
use Kununu\TestingBundle\Test\FixturesContainerGetterTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Tester\CommandTester;

abstract class AbstractCommandTestCase extends FixturesAwareTestCase
{
    use FixturesContainerGetterTrait;

    protected Application $application;
    protected CommandTester $commandTester;

    public function testExistsCommands(): void
    {
        $existingCommandAlias = $this->getExistingCommandAlias();

        $command = $this->application->find($existingCommandAlias);

        self::assertInstanceOf(
            $this->getCommandClass(),
            $command,
            sprintf('Asserted that console command "%s" exists', $existingCommandAlias)
        );

        foreach ($this->getNonExistingCommandAliases() as $nonExistingCommandAlias) {
            try {
                $this->application->find($nonExistingCommandAlias);
                self::fail(sprintf('Console command "%s" should not exist', $nonExistingCommandAlias));
            } catch (CommandNotFoundException) {
                self::assertTrue(
                    true,
                    sprintf('Asserted that console command "%s" does not exist', $nonExistingCommandAlias)
                );
            }
        }
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
        $this->commandTester = new CommandTester($command);
        $this->commandTester->setInputs($inputs);

        $this->commandTester->execute(
            array_merge(['command' => $command->getName()], $args),
            $options
        );
    }

    abstract protected function preRunCommand(): void;

    abstract protected function getCommandClass(): string;

    abstract protected function getExistingCommandAlias(): string;

    abstract protected function getNonExistingCommandAliases(): array;
}
