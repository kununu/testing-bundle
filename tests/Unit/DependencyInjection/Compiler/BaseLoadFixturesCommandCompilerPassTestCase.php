<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Unit\DependencyInjection\Compiler;

use Kununu\TestingBundle\DependencyInjection\Compiler\AbstractLoadFixturesCommandCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

abstract class BaseLoadFixturesCommandCompilerPassTestCase extends BaseCompilerPassTestCase
{
    protected readonly string $sectionName;
    protected readonly string $executorClass;
    protected readonly string $loaderClass;
    protected readonly string $purgerClass;
    protected readonly string $commandClass;

    abstract protected function getCompilerInstance(): AbstractLoadFixturesCommandCompilerPass;

    abstract protected function getSectionName(): string;

    abstract protected function getExecutorClass(): string;

    abstract protected function getLoaderClass(): string;

    abstract protected function getPurgerClass(): string;

    abstract protected function getCommandClass(): string;

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass($this->getCompilerInstance());
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->sectionName = $this->getSectionName();
        $this->executorClass = $this->getExecutorClass();
        $this->loaderClass = $this->getLoaderClass();
        $this->purgerClass = $this->getPurgerClass();
        $this->commandClass = $this->getCommandClass();
    }
}
