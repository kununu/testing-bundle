<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Test\Options;

interface OptionsInterface
{
    public function append(): bool;

    public function clear(): bool;
}
