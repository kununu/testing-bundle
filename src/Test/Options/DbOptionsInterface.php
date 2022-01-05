<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Test\Options;

interface DbOptionsInterface extends OptionsInterface
{
    public function transactional(): bool;
}
