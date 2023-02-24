<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Service;

use Kununu\DataFixtures\Executor\ExecutorInterface;
use Kununu\DataFixtures\Loader\LoaderInterface;

final class Orchestrator implements OrchestratorInterface
{
    public function __construct(private ExecutorInterface $executor, private LoaderInterface $loader)
    {
    }

    public function execute(array $fixturesClassNames, bool $append, bool $clearFixtures = true): void
    {
        if ($clearFixtures) {
            $this->clearFixtures();
        }

        foreach ($fixturesClassNames as $className) {
            $this->loader->loadFromClassName($className);
        }

        $this->executor->execute($this->loader->getFixtures(), $append);
    }

    public function registerInitializableFixture(string $className, mixed ...$args): void
    {
        $this->loader->registerInitializableFixture($className, ...$args);
    }

    public function clearFixtures(): void
    {
        $this->loader->clearFixtures();
    }

    public function getFixtures(): array
    {
        return $this->loader->getFixtures();
    }
}
