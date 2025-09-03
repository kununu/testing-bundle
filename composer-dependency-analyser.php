<?php
declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    ->disableReportingUnmatchedIgnores()
    ->ignoreErrors([ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackages(
        [
            'doctrine/migrations',
            'doctrine/persistence',
            'matthiasnoback/symfony-config-test',
            'symfony/console',
            'symfony/error-handler',
            'symfony/http-client-contracts',
            'symfony/routing',
        ],
        [ErrorType::SHADOW_DEPENDENCY]
    )
    ->setFileExtensions(['php']);
