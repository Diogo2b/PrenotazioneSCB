<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240710075301 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sector (id INT AUTO_INCREMENT NOT NULL, tribune_id INT NOT NULL, name VARCHAR(255) NOT NULL, sigle VARCHAR(10) DEFAULT NULL, numbered_seats TINYINT(1) NOT NULL, capacity INT NOT NULL, available_for_sale TINYINT(1) NOT NULL, INDEX IDX_4BA3D9E8F9E50ED6 (tribune_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sector ADD CONSTRAINT FK_4BA3D9E8F9E50ED6 FOREIGN KEY (tribune_id) REFERENCES tribune (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sector DROP FOREIGN KEY FK_4BA3D9E8F9E50ED6');
        $this->addSql('DROP TABLE sector');
    }
}
