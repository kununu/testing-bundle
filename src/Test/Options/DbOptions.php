<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Test\Options;

/**
 * @method self withAppend()
 * @method self withoutAppend()
 * @method self withClear()
 * @method self withoutClear()
 * @method self withTransactional()
 * @method self withoutTransactional()
 */
class DbOptions extends Options implements DbOptionsInterface
{
    protected const array OPTIONS = [
        'transactional' => true,
    ];

    public static function createNonTransactional(): self
    {
        return self::create()->withoutTransactional();
    }

    public function transactional(): bool
    {
        return $this->getAttribute('transactional');
    }
}
