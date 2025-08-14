<?php
declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function(RoutingConfigurator $routingConfigurator): void {
    $routingConfigurator
        ->import(
            [
                'path'      => __DIR__ . '/../Controller',
                'namespace' => 'Kununu\TestingBundle\Tests\App\Controller',
            ],
            'attribute'
        )
        ->prefix('/app');
};
