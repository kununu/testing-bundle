<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Service\SchemaCopy;

interface SchemaCopyAdapterInterface
{
    public function disableConstraints(): void;

    public function enableConstraints(): void;

    public function getTableCreateStatement(string $table): string;

    public function getTables(): array;

    public function getViewCreateStatement(string $view): string;

    public function getViews(): array;

    public function purgeTablesAndViews(): void;

    public function runCopy(callable $fn): void;

    public function sameTypeAs(SchemaCopyAdapterInterface $other): bool;

    public function type(): string;
}
