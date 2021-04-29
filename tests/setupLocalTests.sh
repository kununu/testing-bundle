#!/usr/bin/env bash
cd "$(dirname "${BASH_SOURCE[0]}")" || exit 1
if ! [ -f App/.env.test ]; then
  echo
  echo "Could not found local test env configurations"
  echo
  echo "Copy App/tests/.env to App/tests/.env.test and setup according your local env"
  echo "Then run this script again"
  echo
  exit 1
fi
cd ../
echo
echo "(Re-)Installing composer dependencies..."
rm -rf vendor/*
rm composer.lock
composer install
echo
echo "Cleaning test application caches"
rm -rf tests/App/var/*
echo
echo "Creating local test env databases..."
php tests/App/bin/console --env test doctrine:database:create --connection=def --if-not-exists
php tests/App/bin/console --env test doctrine:database:create --connection=monolithic --if-not-exists
echo
echo "Running migrations for local test env..."
php tests/App/bin/console --env test doctrine:migrations:migrate -n --conn=def || echo "No migrations found or migration failed"
php tests/App/bin/console --env test doctrine:migrations:migrate -n --conn=monolithic || echo "No migrations found or migration failed"
echo
echo "DONE!"
echo "You can now run your tests by running vendor/bin/phpunit"
echo
