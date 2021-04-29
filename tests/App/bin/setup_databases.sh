#!/usr/bin/env bash
cd "$(dirname "${BASH_SOURCE[0]}")" || exit 1

if [ -z "$1" ]; then
  command_env=""
else
  command_env="--env ${1}"
fi

echo "Creating Databases..."
php console doctrine:database:create ${command_env} --connection=def --if-not-exists
php console doctrine:database:create ${command_env} --connection=monolithic --if-not-exists
echo "Databases created!"
echo
echo "Running Migrations..."
php console doctrine:migrations:migrate ${command_env} -n --conn=def || echo "No migrations found or migration failed"
php console doctrine:migrations:migrate ${command_env} -n --conn=monolithic || echo "No migrations found or migration failed"
echo "Migrations ran!"
echo
echo "Creating Elasticsearch index..."
php console app:elasticsearch:create-index ${command_env}
echo "Elasticsearch index created!"
echo
echo "Databases setup complete!"
