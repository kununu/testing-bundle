<?php
declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitSelfCallRector;
use Rector\PHPUnit\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector;
use Rector\Privatization\Rector\Class_\FinalizeTestCaseClassRector;

return RectorConfig::configure()
    ->withPhpSets()
    ->withAttributesSets(symfony: true, phpunit: true)
    ->withComposerBased(phpunit: true, symfony: true)
    ->withRules([
        FinalizeTestCaseClassRector::class,
        PreferPHPUnitSelfCallRector::class,
    ])
    ->withSkip([
        __DIR__ . '/rector-ci.php',
        __DIR__ . '/composer-dependency-analyser.php',
        __DIR__ . '/tests/App/config/bundles.php',
        AddDoesNotPerformAssertionToNonAssertingTestRector::class => [
            __DIR__ . '/tests/Integration/Command/AbstractFixturesCommandTestCase.php',
        ],
    ])
    ->withSkipPath(__DIR__ . '/tests/App/var/*')
    ->withImportNames();
