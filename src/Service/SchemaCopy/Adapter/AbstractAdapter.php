<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Service\SchemaCopy\Adapter;

use Doctrine\DBAL\Connection;
use Kununu\TestingBundle\Service\SchemaCopy\SchemaCopyAdapterInterface;
use Kununu\TestingBundle\Traits\ConnectionToolsTrait;

abstract class AbstractAdapter implements SchemaCopyAdapterInterface
{
    use ConnectionToolsTrait;

    protected const TYPE = '';

    public function __construct(protected Connection $connection)
    {
    }

    public function runCopy(callable $fn): void
    {
        $this->disableConstraints();
        $fn();
        $this->enableConstraints();
    }

    public function type(): string
    {
        return static::TYPE;
    }

    public function sameTypeAs(SchemaCopyAdapterInterface $other): bool
    {
        return $this->type() === $other->type();
    }
}
