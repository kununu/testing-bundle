<?php
declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Kununu\TestingBundle\Tests\App\LegacyKernel;
use Kununu\TestingBundle\Tests\App\NewKernel;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

// Support changes introduced in Symfony 5.2
if (BaseKernel::MAJOR_VERSION >= 5 && BaseKernel::MINOR_VERSION >= 2) {
    $_SERVER['KERNEL_CLASS'] = NewKernel::class;
} else {
    $_SERVER['KERNEL_CLASS'] = LegacyKernel::class;
}
