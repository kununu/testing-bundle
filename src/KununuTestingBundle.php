<?php
declare(strict_types=1);

namespace Kununu\TestingBundle;

use Kununu\TestingBundle\DependencyInjection\Compiler\CachePoolCompilerPass;
use Kununu\TestingBundle\DependencyInjection\Compiler\ConnectionCompilerPass;
use Kununu\TestingBundle\DependencyInjection\Compiler\CopyConnectionSchemaCommandCompilerPass;
use Kununu\TestingBundle\DependencyInjection\Compiler\DynamoDbCompilerPass;
use Kununu\TestingBundle\DependencyInjection\Compiler\ElasticsearchCompilerPass;
use Kununu\TestingBundle\DependencyInjection\Compiler\HttpClientCompilerPass;
use Kununu\TestingBundle\DependencyInjection\Compiler\NonTransactionalConnectionCompilerPass;
use Kununu\TestingBundle\DependencyInjection\Compiler\OpenSearchCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class KununuTestingBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new CachePoolCompilerPass());
        $container->addCompilerPass(new ConnectionCompilerPass());
        $container->addCompilerPass(new NonTransactionalConnectionCompilerPass());
        $container->addCompilerPass(new DynamoDbCompilerPass());
        $container->addCompilerPass(new ElasticsearchCompilerPass());
        $container->addCompilerPass(new OpenSearchCompilerPass());
        $container->addCompilerPass(new HttpClientCompilerPass());
        $container->addCompilerPass(new CopyConnectionSchemaCommandCompilerPass());
    }
}
