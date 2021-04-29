<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\App;

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class NewKernel extends AbstractKernel
{
    protected const CONFIG_EXTS = '{php,xml,yaml,yml}';

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $confDir = $this->getProjectDir() . '/config';

        $routes->import($confDir . '/{routes}/' . $this->environment . '/*.' . NewKernel::CONFIG_EXTS);
        $routes->import($confDir . '/{routes}/*.' . NewKernel::CONFIG_EXTS);
        $routes->import($confDir . '/{routes}.' . NewKernel::CONFIG_EXTS);
    }
}
