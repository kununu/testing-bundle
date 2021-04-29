#!/usr/bin/env bash
cd "$(dirname "${BASH_SOURCE[0]}")" || exit 1
if ! [ -f App/.env.test ]; then
  echo "Could not found local test env configurations!"
  echo "Copy App/tests/.env to App/tests/.env.test & App/tests/.env.local and setup according your local env."
  echo "Then run this script again!!"
  exit 1
fi
cd ../
echo
echo "(Re-)Installing composer dependencies..."
rm -rf vendor/*
rm composer.lock
composer install
echo
echo "Cleaning application caches"
rm -rf tests/App/var/*
echo
source tests/App/bin/setup_databases.sh
echo "DONE!"
echo "You can now run your tests by running vendor/bin/phpunit"
echo
