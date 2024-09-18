<?php
declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\ErrorHandler;

// PHPUnit 11 conflicts with Symfony error handler and make all functional tests being marked as risky
// with "Test code or tested code did not remove its own exception handlers"
// https://github.com/symfony/symfony/issues/53812#issuecomment-1962740145
set_exception_handler([new ErrorHandler(), 'handleException']);

(new Dotenv())->bootEnv(dirname(__DIR__) . '/tests/App/.env');
