<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Service\SchemaCopy;

use Doctrine\DBAL\Connection;

interface SchemaCopyAdapterFactoryInterface
{
    public function createAdapter(Connection $connection): SchemaCopyAdapterInterface;
}
