#!/usr/bin/env bash
cd "$(dirname "${BASH_SOURCE[0]}")" || exit 1

echo "Creating Databases..."
php console doctrine:database:create --connection=def --if-not-exists
php console doctrine:database:create --connection=monolithic --if-not-exists
echo "Databases created!"
echo
echo "Running Migrations..."
php console doctrine:migrations:migrate -n --conn=def || echo "No migrations found or migration failed"
php console doctrine:migrations:migrate -n --conn=monolithic || echo "No migrations found or migration failed"
echo "Migrations ran!"
echo
echo "Creating Elasticsearch index..."
php console app:elasticsearch:create-index
echo "Elasticsearch index created!"
echo
echo "Databases setup complete!"
