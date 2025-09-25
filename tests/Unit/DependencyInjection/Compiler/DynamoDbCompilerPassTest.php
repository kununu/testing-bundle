<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Unit\DependencyInjection\Compiler;

use Kununu\DataFixtures\Executor\DynamoDbExecutor;
use Kununu\DataFixtures\Loader\DynamoDbFixturesLoader;
use Kununu\DataFixtures\Purger\DynamoDbPurger;
use Kununu\TestingBundle\Command\LoadDynamoDbFixturesCommand;
use Kununu\TestingBundle\DependencyInjection\Compiler\DynamoDbCompilerPass;
use Kununu\TestingBundle\DependencyInjection\KununuTestingExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class DynamoDbCompilerPassTest extends BaseLoadFixturesCommandCompilerPassTestCase
{
    private const array DYNAMO_DB_SERVICES = [
        'dynamo_db_service_1' => [
            'service'                                 => 'aws.dynamo_db.client.default',
            'table_names'                             => ['table1', 'table2'],
            'load_command_fixtures_classes_namespace' => [
                'App\Fixtures\DynamoDb\DynamoDbFixture1',
                'App\Fixtures\DynamoDb\DynamoDbFixture2',
            ],
        ],
        'dynamo_db_service_2' => [
            'service'     => 'aws.dynamo_db.client.secondary',
            'table_names' => ['table3'],
        ],
    ];

    private const array CONFIG = [
        'dynamo_db' => self::DYNAMO_DB_SERVICES,
    ];

    public function testCreatesOrchestratorForEachDynamoDbService(): void
    {
        $this->container->loadFromExtension(KununuTestingExtension::ALIAS, self::CONFIG);

        $this->doAssertionsOnDynamoDbServices(
            function(
                string $purgerId,
                string $executorId,
                string $loaderId,
                string $orchestratorId,
                string $dynamoDbAlias,
                array $config,
                ?string $consoleCommandId,
                ?string $consoleCommandName,
            ): void {
                $expectedTableNames = $config['table_names'] ?? [];

                $this->assertPurger(
                    $purgerId,
                    DynamoDbPurger::class,
                    new Reference($config['service']),
                    $expectedTableNames
                );
                $this->assertExecutor(
                    $executorId,
                    DynamoDbExecutor::class,
                    new Reference($config['service']),
                    new Reference($purgerId)
                );
                $this->assertLoader($loaderId, DynamoDbFixturesLoader::class);
                $this->assertOrchestrator($orchestratorId, $executorId, $loaderId);

                if (null !== $consoleCommandId) {
                    $this->assertContainerBuilderHasService($consoleCommandId);

                    $this->assertFixturesCommand(
                        $consoleCommandId,
                        $consoleCommandName,
                        LoadDynamoDbFixturesCommand::class,
                        $dynamoDbAlias,
                        $orchestratorId,
                        $config['load_command_fixtures_classes_namespace'] ?? []
                    );
                }
            }
        );
    }

    public function testCompileWithoutDynamoDbParameter(): void
    {
        $this->compile();

        foreach ($this->container->getServiceIds() as $serviceId) {
            self::assertDoesNotMatchRegularExpression(
                '/^kununu_testing\.orchestrator\.dynamo_db\.\w+$/m',
                $serviceId
            );
            self::assertDoesNotMatchRegularExpression(
                '/^kununu_testing\.orchestrator\.dynamo_db\.\w+\.purger$/m',
                $serviceId
            );
            self::assertDoesNotMatchRegularExpression(
                '/^kununu_testing\.orchestrator\.dynamo_db\.\w+\.executor$/m',
                $serviceId
            );
            self::assertDoesNotMatchRegularExpression(
                '/^kununu_testing\.orchestrator\.dynamo_db\.\w+\.loader$/m',
                $serviceId
            );
            self::assertDoesNotMatchRegularExpression(
                '/^kununu_testing\.load_fixtures\.dynamo_db\.\w+\.command$/m',
                $serviceId
            );
        }
    }

    protected function getCompilerInstance(): DynamoDbCompilerPass
    {
        return new DynamoDbCompilerPass();
    }

    protected function getSectionName(): string
    {
        return 'dynamo_db';
    }

    protected function getExecutorClass(): string
    {
        return DynamoDbExecutor::class;
    }

    protected function getLoaderClass(): string
    {
        return DynamoDbFixturesLoader::class;
    }

    protected function getPurgerClass(): string
    {
        return DynamoDbPurger::class;
    }

    protected function getCommandClass(): string
    {
        return LoadDynamoDbFixturesCommand::class;
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        parent::registerCompilerPass($container);

        if ($this->name() !== 'testThatDoesNotCreateServicesWhenContainerDoesNotHaveKununuTestingExtension') {
            $container->registerExtension(new KununuTestingExtension());
        }

        foreach (self::DYNAMO_DB_SERVICES as $config) {
            $dynamoDbServiceDefinition = new Definition();
            $this->setDefinition($config['service'], $dynamoDbServiceDefinition);
        }
    }

    private function doAssertionsOnDynamoDbServices(callable $asserter): void
    {
        $this->compile();

        foreach (self::DYNAMO_DB_SERVICES as $dynamoDbAlias => $config) {
            $createsCommand = !empty($config['load_command_fixtures_classes_namespace']);

            $purgerId = sprintf('kununu_testing.orchestrator.dynamo_db.%s.purger', $dynamoDbAlias);
            $executorId = sprintf('kununu_testing.orchestrator.dynamo_db.%s.executor', $dynamoDbAlias);
            $loaderId = sprintf('kununu_testing.orchestrator.dynamo_db.%s.loader', $dynamoDbAlias);
            $orchestratorId = sprintf('kununu_testing.orchestrator.dynamo_db.%s', $dynamoDbAlias);
            $consoleCommandId = $createsCommand
                ? sprintf('kununu_testing.load_fixtures.dynamo_db.%s.command', $dynamoDbAlias)
                : null;
            $consoleCommandName = $createsCommand
                ? sprintf('kununu_testing:load_fixtures:dynamo_db:%s', $dynamoDbAlias)
                : null;

            $asserter(
                $purgerId,
                $executorId,
                $loaderId,
                $orchestratorId,
                $dynamoDbAlias,
                $config,
                $consoleCommandId,
                $consoleCommandName
            );
        }
    }
}
