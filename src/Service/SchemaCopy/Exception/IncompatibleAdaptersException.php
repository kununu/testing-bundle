<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Service\SchemaCopy\Exception;

use Kununu\TestingBundle\Service\SchemaCopy\SchemaCopyAdapterInterface;
use RuntimeException;

final class IncompatibleAdaptersException extends RuntimeException
{
    public function __construct(SchemaCopyAdapterInterface $source, SchemaCopyAdapterInterface $destination)
    {
        parent::__construct(sprintf(
            'Source and destination adapters must be of the same type! Source: %s Destination: %s',
            $source->type(),
            $destination->type()
        ));
    }
}
