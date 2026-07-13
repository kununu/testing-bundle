# AGENTS.md

## What this is

`kununu/testing-bundle` is a Symfony bundle (`type: symfony-bundle`) that makes
testing easier. It integrates with [`kununu/data-fixtures`](https://github.com/kununu/data-fixtures)
to load fixtures in tests and provides helpers such as `WebTestCase`, a
`RequestBuilder`, and a database schema copier.

It is a library, not a deployed service, and is intended for `dev`/`test`
environments only — never production.

## Domain

Fixture loading and test utilities for Symfony applications. Supported fixture
types: Doctrine DBAL connections, cache pools, DynamoDB, Elasticsearch,
OpenSearch, and Symfony HTTP Client.

## Code layout

- `src/Command/` — console commands that load fixtures and copy schemas.
- `src/DependencyInjection/` — bundle extension, `Configuration`, and compiler
  passes wiring each fixture type.
- `src/Service/` — the `Orchestrator` and the `SchemaCopy` adapters/factory.
- `src/Test/` — consumer-facing test helpers (`FixturesAwareTestCase`,
  `WebTestCase`, `RequestBuilder`, options).
- `src/Resources/config/` — service definitions.
- `src/KununuTestingBundle.php` — bundle entrypoint.
- `tests/` — `Unit/` and `Integration/` suites plus a test app under `tests/App/`.
- `docs/` — per-feature documentation.

PSR-4: `Kununu\TestingBundle\` → `src/`, `Kununu\TestingBundle\Tests\` → `tests/`.

## Quality gates

Defined as `composer` scripts (see `scripts` in `composer.json`):

- `composer cs` — PHP CS Fixer (kununu standards).
- `composer sniffer` — PHP_CodeSniffer (`phpcs.xml.dist`).
- `composer phpstan` — PHPStan (`phpstan.neon.dist`).
- `composer rector` — Rector (`rector-ci.php`), dry-run.
- `composer unit` / `composer integration` / `composer test` — PHPUnit suites.

CI runs via the "Continuous Integration" GitHub Actions workflow; SonarCloud
gates quality. Full workflow is in `CONTRIBUTING.md`.

## Hard constraints

- Stay compatible with the version ranges pinned in `composer.json` (`require`),
  including Symfony `^6.4 || ^7.4` and `doctrine/dbal ^3.10 || ^4.4`.
- PHP version is pinned in `composer.json` (`require.php`).
- Integration tests need real backing services (MySQL, Elasticsearch, OpenSearch);
  see `CONTRIBUTING.md`.
- This bundle must not be enabled in production.
