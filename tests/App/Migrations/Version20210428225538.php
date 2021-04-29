<?php

declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210428225538 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create database tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
CREATE TABLE IF NOT EXISTS table_1 (
    name VARCHAR(255) NOT NULL,
    description VARCHAR(255) NOT NULL
) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;
SQL
        );

        $this->addSql(<<<'SQL'
CREATE TABLE IF NOT EXISTS table_2 (
    name VARCHAR(255) NOT NULL,
    description VARCHAR(255) NOT NULL
 ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;
SQL
        );

        $this->addSql(<<<'SQL'
CREATE TABLE IF NOT EXISTS table_3 (
    name VARCHAR(255) NOT NULL,
    description VARCHAR(255) NOT NULL, PRIMARY KEY(name)
) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;
SQL
        );

        $this->addSql(<<<'SQL'
CREATE TABLE IF NOT EXISTS table_to_exclude (
    name VARCHAR(255) NOT NULL,
    description VARCHAR(255) NOT NULL
) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;
SQL
        );
    }

    public function down(Schema $schema): void
    {
        // I don't care!
        $this->throwIrreversibleMigrationException();
    }
}
