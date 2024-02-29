<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Service\SchemaCopy\Exception;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use RuntimeException;

final class UnsupportedDatabasePlatformException extends RuntimeException
{
    public function __construct(AbstractPlatform $platform)
    {
        parent::__construct(sprintf('Unsupported database platform %s', $platform::class));
    }
}
