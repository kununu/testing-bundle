<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Service;

use Kununu\DataFixtures\Executor\ExecutorInterface;
use Kununu\DataFixtures\Loader\LoaderInterface;

final class Orchestrator
{
    private $executor;
    private $loader;

    public function __construct(ExecutorInterface $executor, LoaderInterface $loader)
    {
        $this->executor = $executor;
        $this->loader = $loader;
    }

    public function execute(array $fixturesClassNames, bool $append): void
    {
        $this->loader->clearFixtures();

        foreach ($fixturesClassNames as $className) {
            $this->loader->loadFromClassName($className);
        }

        $this->executor->execute($this->loader->getFixtures(), $append);
    }

    public function registerInitializableFixture(string $className, ...$args): void
    {
        $this->loader->registerInitializableFixture($className, ...$args);
    }
}
