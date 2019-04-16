<?php declare(strict_types=1);

namespace Kununu\TestingBundle\Test;

use Kununu\TestingBundle\Service\Orchestrator;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

abstract class FixturesAwareTestCase extends BaseWebTestCase
{
    final protected function loadDbFixtures(string $connectionName, array $classNames = [], bool $append = false)
    {
        /** @var Orchestrator $orchestrator */
        $orchestrator = $this->getContainer()->get(sprintf('kununu_testing.orchestrator.connections.%s', $connectionName));

        $orchestrator->execute($classNames, $append);
    }

    final protected function loadCachePoolFixtures(string $cachePoolServiceId, array $classNames = [], bool $append = false) : void
    {
        /** @var Orchestrator $orchestrator */
        $orchestrator = $this->getContainer()->get(sprintf('kununu_testing.orchestrator.cache_pools.%s', $cachePoolServiceId));

        $orchestrator->execute($classNames, $append);
    }

    final protected function getContainer() : ContainerInterface
    {
        $kernel = $this->createKernel();
        $kernel->boot();
        $container = $kernel->getContainer();

        if ($container->has('test.service_container')) {
            return $container->get('test.service_container');
        } else {
            return $container;
        }
    }
}
