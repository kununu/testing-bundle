<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Service;

interface OrchestratorInterface
{
    public function execute(array $fixturesClassNames, bool $append, bool $clearFixtures = true): void;

    public function registerInitializableFixture(string $className, mixed ...$args): void;

    public function clearFixtures(): void;

    public function getFixtures(): array;
}
