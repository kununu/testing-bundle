<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Test\Options;

class DbOptions extends Options implements DbOptionsInterface
{
    private $transactional = true;

    public function transactional(): bool
    {
        return $this->transactional;
    }

    /** @return static */
    public static function createNonTransactional(): self
    {
        return self::create()->withoutTransactional();
    }

    /** @return static */
    public function withTransactional(): self
    {
        $this->transactional = true;

        return $this;
    }

    /** @return static */
    public function withoutTransactional(): self
    {
        $this->transactional = false;

        return $this;
    }
}
