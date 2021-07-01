<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Command;

abstract class AbstractFixturesCommandTestCase extends AbstractCommandTestCase
{
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

    abstract protected function doAssertionsForExecuteAppend(): void;

    abstract protected function doAssertionsForExecuteNonAppendInteractive(): void;

    abstract protected function doAssertionsForExecuteNonAppendNonInteractive(): void;
}
