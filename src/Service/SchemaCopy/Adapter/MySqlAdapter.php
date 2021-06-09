<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Service\SchemaCopy\Adapter;

final class MySqlAdapter extends AbstractAdapter
{
    protected const TYPE = 'MySql';

    public function disableConstraints(): void
    {
        $this->executeQuery($this->connection, 'SET UNIQUE_CHECKS=0');
        $this->executeQuery($this->connection, 'SET FOREIGN_KEY_CHECKS=0');
    }

    public function enableConstraints(): void
    {
        $this->executeQuery($this->connection, 'SET UNIQUE_CHECKS=1');
        $this->executeQuery($this->connection, 'SET FOREIGN_KEY_CHECKS=1');
    }

    public function getTableCreateStatement(string $table): string
    {
        return $this->fetchColumn($this->connection, sprintf('SHOW CREATE TABLE `%s`', $table), 1) ?: '';
    }

    public function getTables(): array
    {
        return $this->fetchAllFirstColumn($this->connection, 'SHOW FULL TABLES WHERE Table_type = \'BASE TABLE\'');
    }

    public function getViewCreateStatement(string $view): string
    {
        return $this->fetchColumn($this->connection, sprintf('SHOW CREATE VIEW `%s`', $view), 1) ?: '';
    }

    public function getViews(): array
    {
        return $this->fetchAllFirstColumn($this->connection, 'SHOW FULL TABLES WHERE Table_type = \'VIEW\'');
    }

    public function purgeTablesAndViews(): void
    {
        foreach ($this->getViews() as $view) {
            $this->executeQuery($this->connection, sprintf('DROP VIEW IF EXISTS `%s`', $view));
        }

        foreach ($this->getTables() as $table) {
            $this->executeQuery($this->connection, sprintf('DROP TABLE IF EXISTS `%s`', $table));
        }
    }
}
