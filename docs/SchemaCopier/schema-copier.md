# Doctrine DBAL Schema Copier

In some situations you might want/have the need to copy one database schema from one database to another.

For example, your service might being used for some migration process where you have your service database handled by Doctrine Migrations, but you are also consuming an external database from a legacy component.

So when you are running your tests you might have the need to recreate your databases for that purpose, but the external database schema definitions is in another service/application.

This bundle will provide you with a command that can fetch the schema for that external database and recreate it (not with data) in another database (e.g., your testing database)..

-----------------------

## Symfony Command to copy schemas

This bundle will automatically create a Symfony Command allow copying databases schemas (if you have at least a Doctrine connection configured).

```shell
php bin/console kununu_testing:connections:schema:copy --from SOURCE --to DESTINATION
```

`SOURCE` and `DESTINATION` refer to the connection names in your `doctrine.yaml` configuration file (`doctrine:dbal:connections` keys).

**WARNING**

Be aware that this command will **DESTROY** all tables and views (including data) on your *DESTINATION* database schema!

Please take caution when using it.

---

[Back to Index](../../README.md)
