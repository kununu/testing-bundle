<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Service\SchemaCopy;

use Doctrine\DBAL\Connection;

interface SchemaCopyInterface
{
    public function copy(Connection $source, Connection $destination): void;
}
