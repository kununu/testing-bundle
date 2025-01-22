<?php
declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitSelfCallRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Privatization\Rector\Class_\FinalizeTestCaseClassRector;
use Rector\Symfony\Set\SymfonySetList;

return RectorConfig::configure()
    ->withPhpSets(php83: true)
    ->withAttributesSets(symfony: true, phpunit: true)
    ->withSets([
        PHPUnitSetList::PHPUNIT_110,
        SymfonySetList::SYMFONY_64,
    ])
    ->withRules([
        FinalizeTestCaseClassRector::class,
        PreferPHPUnitSelfCallRector::class,
    ])
    ->withSkip([
        __DIR__ . '/rector-ci.php',
        __DIR__ . '/tests/App/config/bundles.php',
        AddOverrideAttributeToOverriddenMethodsRector::class,
    ])
    ->withSkipPath(__DIR__ . '/tests/App/var/*')
    ->withImportNames();
