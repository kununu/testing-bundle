<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Test\Options;

abstract class AbstractOptions implements OptionsInterface
{
    private $append = false;
    private $clear = true;

    private function __construct()
    {
    }

    public function append(): bool
    {
        return $this->append;
    }

    public function clear(): bool
    {
        return $this->clear;
    }

    /** @return static */
    public static function create(): self
    {
        return new static();
    }

    /** @return static */
    public function withAppend(): self
    {
        $this->append = true;

        return $this;
    }

    /** @return static */
    public function withoutAppend(): self
    {
        $this->append = false;

        return $this;
    }

    /** @return static */
    public function withClear(): self
    {
        $this->clear = true;

        return $this;
    }

    /** @return static */
    public function withoutClear(): self
    {
        $this->clear = false;

        return $this;
    }
}
