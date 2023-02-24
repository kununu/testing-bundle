<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Test\Options;

abstract class AbstractOptions
{
    protected const OPTIONS = [];

    private const PREFIX_WITH = 'with';
    private const PREFIX_WITHOUT = 'without';

    private array $options = [];

    protected function __construct()
    {
        $defaultValues = static::OPTIONS;
        foreach (class_parents($this) as $parentClass) {
            $defaultValues = array_merge($defaultValues, $parentClass::OPTIONS);
        }

        foreach ($defaultValues as $param => $value) {
            $this->options[$param] = $value;
        }
    }

    public static function create(): static
    {
        return new static();
    }

    public function __call(string $method, array $args): static
    {
        foreach ([self::PREFIX_WITHOUT => false, self::PREFIX_WITH => true] as $prefix => $value) {
            if ($prefix === substr($method, 0, $setterPrefixLen = strlen($prefix))) {
                $option = lcfirst(substr($method, $setterPrefixLen));
                if (array_key_exists($option, $this->options)) {
                    $this->options[$option] = $value;
                }
            }
        }

        return $this;
    }

    protected function getAttribute($name): bool
    {
        return array_key_exists($name, $this->options) ? $this->options[$name] : false;
    }
}
