#!/usr/bin/env bash
cd "$(dirname "${BASH_SOURCE[0]}")" || exit 1
if ! [ -f App/.env.test ]; then
  echo "Could not found local test env configurations!"
  echo "Copy tests/App/.env to tests/App/.env.test & tests/App/.env.local and setup according your local env."
  echo "Then run this script again!!"
  exit 1
fi
cd ../
echo
echo "(Re-)Installing composer dependencies..."
rm -rf vendor/*
rm composer.lock
composer update --prefer-stable
echo
echo "Cleaning application caches"
rm -rf tests/App/var/*
echo
. tests/App/bin/setup_databases.sh test
echo "DONE!"
echo "You can now run your tests by running composer test"
echo
