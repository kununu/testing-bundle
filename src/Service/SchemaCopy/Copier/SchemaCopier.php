<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Service\SchemaCopy\Copier;

use Doctrine\DBAL\Connection;
use Kununu\TestingBundle\Service\SchemaCopy\Exception\IncompatibleAdaptersException;
use Kununu\TestingBundle\Service\SchemaCopy\SchemaCopyAdapterFactoryInterface;
use Kununu\TestingBundle\Service\SchemaCopy\SchemaCopyInterface;

final class SchemaCopier implements SchemaCopyInterface
{
    public function __construct(private readonly SchemaCopyAdapterFactoryInterface $adapterFactory)
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
                    $destination->executeStatement($sourceAdapter->getTableCreateStatement($table));
                }

                foreach ($sourceAdapter->getViews() as $view) {
                    $destination->executeStatement($sourceAdapter->getViewCreateStatement($view));
                }
            }
        );
    }
}
