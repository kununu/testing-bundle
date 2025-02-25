<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Test\Options;

/**
 * @method self withAppend()
 * @method self withoutAppend()
 * @method self withClear()
 * @method self withoutClear()
 */
class Options extends AbstractOptions implements OptionsInterface
{
    protected const array OPTIONS = [
        'append' => false,
        'clear'  => true,
    ];

    public function append(): bool
    {
        return $this->getAttribute('append');
    }

    public function clear(): bool
    {
        return $this->getAttribute('clear');
    }
}
