# Contributing

Contributions are welcome via Pull Requests on
[GitHub](https://github.com/kununu/testing-bundle).

## Requirements

- PHP as pinned in `composer.json` (`require.php`).
- [Composer](https://getcomposer.org/).
- For integration tests: the `pdo_mysql` extension, a MySQL server, an
  Elasticsearch cluster, and an OpenSearch cluster.

## Getting started

Install dependencies:

```shell
composer install
```

To prepare a local environment for the integration tests, copy `tests/App/.env`
to `tests/App/.env.test` and `tests/App/.env.local`, adjust them for your local
services, then run:

```shell
./tests/setupLocalTests.sh
```

This reinstalls dependencies and provisions the test databases via
`tests/App/bin/setup_databases.sh`.

## Quality gates

The following `composer` scripts are available (see `scripts` in `composer.json`
for the full list):

| Script | Purpose |
| --- | --- |
| `composer cs` | Apply PHP CS Fixer with kununu standards |
| `composer sniffer` / `composer sniffer-fix` | Run PHP_CodeSniffer (`phpcs.xml.dist`) |
| `composer phpstan` | Run PHPStan (`phpstan.neon.dist`) |
| `composer rector` / `composer rector-fix` | Run Rector (`rector-ci.php`) |
| `composer unit` | Run the Unit test suite |
| `composer integration` | Run the Integration test suite |
| `composer test` | Run the Unit and Integration suites |

Coverage variants (`*-coverage`) are also defined. Run the checks locally before
opening a Pull Request; the same gates run in CI via the "Continuous Integration"
workflow, and SonarCloud enforces the quality gate.

## Coding standards

kununu coding standards extend PSR-2. The
[`kununu/code-tools`](https://github.com/kununu/code-tools) package is already a
dev dependency and exposes the commands used to meet them (`composer cs`,
`composer sniffer`).

## Pull Requests

- **Add tests.** Changes are not accepted without tests.
- **Document behaviour changes.** Keep `CHANGELOG.md`, `README.md`, and any
  affected files under `docs/` up to date.
- **Follow SemVer.** This project uses [Semantic Versioning](https://semver.org/);
  weigh API changes carefully.
- **Branch from `main`.** `main` is the stable branch; create a branch per change.
- **One Pull Request per change.** Split unrelated work into separate PRs.
- **Be respectful.** See the [Code of Conduct](CODE_OF_CONDUCT.md).

Happy coding!
