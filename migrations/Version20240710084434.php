<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240710084434 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE price_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, price NUMERIC(10, 2) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sport_match ADD price_type_id INT NOT NULL');
        $this->addSql('ALTER TABLE sport_match ADD CONSTRAINT FK_CE27A41CAE6A44CF FOREIGN KEY (price_type_id) REFERENCES price_type (id)');
        $this->addSql('CREATE INDEX IDX_CE27A41CAE6A44CF ON sport_match (price_type_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sport_match DROP FOREIGN KEY FK_CE27A41CAE6A44CF');
        $this->addSql('DROP TABLE price_type');
        $this->addSql('DROP INDEX IDX_CE27A41CAE6A44CF ON sport_match');
        $this->addSql('ALTER TABLE sport_match DROP price_type_id');
    }
}
