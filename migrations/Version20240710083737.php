<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240710083737 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE abo_seat (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, seat_id INT NOT NULL, start_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', finish_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_3AB32E6FA76ED395 (user_id), INDEX IDX_3AB32E6FC1DAFE35 (seat_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE abo_seat ADD CONSTRAINT FK_3AB32E6FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE abo_seat ADD CONSTRAINT FK_3AB32E6FC1DAFE35 FOREIGN KEY (seat_id) REFERENCES seat (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE abo_seat DROP FOREIGN KEY FK_3AB32E6FA76ED395');
        $this->addSql('ALTER TABLE abo_seat DROP FOREIGN KEY FK_3AB32E6FC1DAFE35');
        $this->addSql('DROP TABLE abo_seat');
    }
}
