<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

abstract class AbstractLoadFixturesCommandCompilerPass extends AbstractCompilerPass
{
    final protected const string LOAD_COMMAND_CLASSES_NAMESPACE_CONFIG = 'load_command_fixtures_classes_namespace';

    private const string ORCHESTRATOR_SERVICE_PREFIX = 'kununu_testing.orchestrator.%s';

    protected readonly string $sectionName;
    protected readonly string $orchestratorServicePrefix;
    protected readonly string $commandClass;
    protected readonly string $purgerClass;
    protected readonly string $executorClass;
    protected readonly string $loaderClass;

    public function __construct()
    {
        $this->sectionName = $this->getSectionName();
        $this->orchestratorServicePrefix = sprintf(self::ORCHESTRATOR_SERVICE_PREFIX, $this->sectionName);
        $this->commandClass = $this->getCommandClass();
        $this->purgerClass = $this->getPurgerClass();
        $this->executorClass = $this->getExecutorClass();
        $this->loaderClass = $this->getLoaderClass();
    }

    abstract protected function getSectionName(): string;

    abstract protected function getCommandClass(): string;

    abstract protected function getPurgerClass(): string;

    abstract protected function getExecutorClass(): string;

    abstract protected function getLoaderClass(): string;

    protected function buildLoadFixturesCommand(
        ContainerBuilder $container,
        string $fixtureType,
        string $orchestratorId,
        string $commandClassName,
        string $name,
        array $namespace,
    ): void {
        if (empty($namespace)) {
            return;
        }

        $loadFixturesCommandDefinition = new Definition(
            $commandClassName,
            [
                $name,
                new Reference($orchestratorId),
                $namespace,
            ]
        );

        $commandName = sprintf('kununu_testing:load_fixtures:%s:%s', $fixtureType, $name);

        $loadFixturesCommandDefinition->setPublic(true);
        $loadFixturesCommandDefinition->setTags([
            'console.command' => [
                [
                    'command' => $commandName,
                ],
            ],
        ]);

        $container->setDefinition(
            str_replace(':', '.', sprintf('%s.command', $commandName)),
            $loadFixturesCommandDefinition
        );
    }

    protected function buildGenericOrchestrator(
        ContainerBuilder $container,
        string $baseId,
        string $loaderId,
        string $orchestratorId,
        callable $purgerDefinitionBuilder,
        callable $executorDefinitionBuilder,
    ): void {
        parent::registerOrchestrator(
            $container,
            $baseId,
            $loaderId,
            $orchestratorId,
            $purgerDefinitionBuilder,
            $executorDefinitionBuilder,
            $this->loaderClass
        );
    }
}
