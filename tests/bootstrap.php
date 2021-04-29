<?php
declare(strict_types=1);

use Kununu\TestingBundle\Tests\App\LegacyKernel;

require dirname(__DIR__) . '/vendor/autoload.php';

use Kununu\TestingBundle\Tests\App\NewKernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

(new Dotenv())->bootEnv(dirname(__DIR__) . '/tests/App/.env');

// Support changes introduced in Symfony 5.2
if (BaseKernel::MAJOR_VERSION >= 5 && BaseKernel::MINOR_VERSION >= 2) {
    $_SERVER['KERNEL_CLASS'] = NewKernel::class;
} else {
    $_SERVER['KERNEL_CLASS'] = LegacyKernel::class;
}
