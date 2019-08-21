<?php declare(strict_types=1);

namespace Kununu\TestingBundle\Service;

use Kununu\DataFixtures\Executor\ExecutorInterface;
use Kununu\DataFixtures\Loader\LoaderInterface;
use Kununu\DataFixtures\Purger\PurgerInterface;

final class Orchestrator
{
    private $executor;

    private $purger;

    private $loader;

    public function __construct(ExecutorInterface $executor, PurgerInterface $purger, LoaderInterface $loader)
    {
        $this->executor = $executor;
        $this->purger = $purger;
        $this->loader = $loader;
    }

    public function execute(array $fixturesClassNames, bool $append): void
    {
        foreach ($fixturesClassNames as $className) {
            $this->loader->loadFromClassName($className);
        }

        $this->executor->execute($this->loader->getFixtures(), $append);
    }
}
