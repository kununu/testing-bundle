<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\App;

use Symfony\Component\Routing\RouteCollectionBuilder;

/**
 * Legacy kernel for Symfony 4.4.x to 5.1.x
 */
final class LegacyKernel extends AbstractKernel
{
    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        $confDir = $this->getProjectDir() . '/config';

        $routes->import($confDir . '/{routes}/' . $this->environment . '/**/*' . LegacyKernel::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir . '/{routes}/*' . LegacyKernel::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir . '/{routes}' . LegacyKernel::CONFIG_EXTS, '/', 'glob');
    }
}
