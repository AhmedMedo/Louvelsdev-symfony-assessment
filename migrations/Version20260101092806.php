<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260101092806 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE countries (id INT AUTO_INCREMENT NOT NULL, uuid VARCHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, region VARCHAR(100) DEFAULT NULL, sub_region VARCHAR(100) DEFAULT NULL, demonym VARCHAR(100) DEFAULT NULL, population INT DEFAULT NULL, independent TINYINT(1) DEFAULT NULL, flag VARCHAR(500) DEFAULT NULL, currency_name VARCHAR(100) DEFAULT NULL, currency_symbol VARCHAR(10) DEFAULT NULL, UNIQUE INDEX UNIQ_5D66EBADD17F50A6 (uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE countries');
    }
}
