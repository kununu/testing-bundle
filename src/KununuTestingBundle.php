<?php declare(strict_types=1);

namespace Kununu\TestingBundle;

use Kununu\TestingBundle\DependencyInjection\Compiler\CachePoolCompilerPass;
use Kununu\TestingBundle\DependencyInjection\Compiler\DoctrineCompilerPass;
use Kununu\TestingBundle\DependencyInjection\Compiler\ElasticSearchCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class KununuTestingBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new CachePoolCompilerPass());
        $container->addCompilerPass(new DoctrineCompilerPass());
        $container->addCompilerPass(new ElasticSearchCompilerPass());
    }
}
