<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Service\SchemaCopy\Copier;

use Doctrine\DBAL\Connection;
use Kununu\TestingBundle\Service\SchemaCopy\Exception\IncompatibleAdaptersException;
use Kununu\TestingBundle\Service\SchemaCopy\SchemaCopyAdapterFactoryInterface;
use Kununu\TestingBundle\Service\SchemaCopy\SchemaCopyInterface;
use Kununu\TestingBundle\Traits\ConnectionToolsTrait;

final class SchemaCopier implements SchemaCopyInterface
{
    use ConnectionToolsTrait;

    public function __construct(private SchemaCopyAdapterFactoryInterface $adapterFactory)
    {
    }

    public function copy(Connection $source, Connection $destination): void
    {
        $sourceAdapter = $this->adapterFactory->createAdapter($source);
        $destinationAdapter = $this->adapterFactory->createAdapter($destination);

        if (!$sourceAdapter->sameTypeAs($destinationAdapter)) {
            throw new IncompatibleAdaptersException($sourceAdapter, $destinationAdapter);
        }

        $destinationAdapter->runCopy(
            function() use ($sourceAdapter, $destinationAdapter, $destination): void {
                $destinationAdapter->purgeTablesAndViews();

                foreach ($sourceAdapter->getTables() as $table) {
                    $this->executeQuery($destination, $sourceAdapter->getTableCreateStatement($table));
                }

                foreach ($sourceAdapter->getViews() as $view) {
                    $this->executeQuery($destination, $sourceAdapter->getViewCreateStatement($view));
                }
            }
        );
    }
}
