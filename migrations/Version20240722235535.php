<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240722235535 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE abo_seat (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, seat_id INT NOT NULL, start_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', finish_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_3AB32E6FA76ED395 (user_id), INDEX IDX_3AB32E6FC1DAFE35 (seat_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, amount NUMERIC(10, 2) NOT NULL, status TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_6D28840DA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment_ticket (id INT AUTO_INCREMENT NOT NULL, payment_id INT NOT NULL, ticket_id INT NOT NULL, INDEX IDX_B29836354C3A3BB (payment_id), INDEX IDX_B2983635700047D2 (ticket_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE price_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, price NUMERIC(10, 2) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE row (id INT AUTO_INCREMENT NOT NULL, sector_id INT DEFAULT NULL, sigle VARCHAR(255) NOT NULL, capacity INT NOT NULL, INDEX IDX_8430F6DBDE95C867 (sector_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE seat (id INT AUTO_INCREMENT NOT NULL, row_id INT NOT NULL, seat_number INT NOT NULL, INDEX IDX_3D5C366683A269F2 (row_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sector (id INT AUTO_INCREMENT NOT NULL, tribune_id INT NOT NULL, name VARCHAR(255) NOT NULL, sigle VARCHAR(10) DEFAULT NULL, numbered_seats TINYINT(1) DEFAULT NULL, capacity INT NOT NULL, available_for_sale TINYINT(1) NOT NULL, INDEX IDX_4BA3D9E8F9E50ED6 (tribune_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sport_match (id INT AUTO_INCREMENT NOT NULL, price_type_id INT NOT NULL, home_team VARCHAR(255) NOT NULL, away_team VARCHAR(255) DEFAULT NULL, match_date DATE NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_CE27A41CAE6A44CF (price_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ticket (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, sport_match_id INT DEFAULT NULL, price NUMERIC(10, 2) NOT NULL, status TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_97A0ADA3A76ED395 (user_id), INDEX IDX_97A0ADA31C1C536C (sport_match_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tribune (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, sigle VARCHAR(10) DEFAULT NULL, numbered_seats TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE abo_seat ADD CONSTRAINT FK_3AB32E6FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE abo_seat ADD CONSTRAINT FK_3AB32E6FC1DAFE35 FOREIGN KEY (seat_id) REFERENCES seat (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE payment_ticket ADD CONSTRAINT FK_B29836354C3A3BB FOREIGN KEY (payment_id) REFERENCES payment (id)');
        $this->addSql('ALTER TABLE payment_ticket ADD CONSTRAINT FK_B2983635700047D2 FOREIGN KEY (ticket_id) REFERENCES ticket (id)');
        $this->addSql('ALTER TABLE row ADD CONSTRAINT FK_8430F6DBDE95C867 FOREIGN KEY (sector_id) REFERENCES sector (id)');
        $this->addSql('ALTER TABLE seat ADD CONSTRAINT FK_3D5C366683A269F2 FOREIGN KEY (row_id) REFERENCES row (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sector ADD CONSTRAINT FK_4BA3D9E8F9E50ED6 FOREIGN KEY (tribune_id) REFERENCES tribune (id)');
        $this->addSql('ALTER TABLE sport_match ADD CONSTRAINT FK_CE27A41CAE6A44CF FOREIGN KEY (price_type_id) REFERENCES price_type (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA31C1C536C FOREIGN KEY (sport_match_id) REFERENCES sport_match (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE abo_seat DROP FOREIGN KEY FK_3AB32E6FA76ED395');
        $this->addSql('ALTER TABLE abo_seat DROP FOREIGN KEY FK_3AB32E6FC1DAFE35');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840DA76ED395');
        $this->addSql('ALTER TABLE payment_ticket DROP FOREIGN KEY FK_B29836354C3A3BB');
        $this->addSql('ALTER TABLE payment_ticket DROP FOREIGN KEY FK_B2983635700047D2');
        $this->addSql('ALTER TABLE row DROP FOREIGN KEY FK_8430F6DBDE95C867');
        $this->addSql('ALTER TABLE seat DROP FOREIGN KEY FK_3D5C366683A269F2');
        $this->addSql('ALTER TABLE sector DROP FOREIGN KEY FK_4BA3D9E8F9E50ED6');
        $this->addSql('ALTER TABLE sport_match DROP FOREIGN KEY FK_CE27A41CAE6A44CF');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3A76ED395');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA31C1C536C');
        $this->addSql('DROP TABLE abo_seat');
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE payment_ticket');
        $this->addSql('DROP TABLE price_type');
        $this->addSql('DROP TABLE row');
        $this->addSql('DROP TABLE seat');
        $this->addSql('DROP TABLE sector');
        $this->addSql('DROP TABLE sport_match');
        $this->addSql('DROP TABLE ticket');
        $this->addSql('DROP TABLE tribune');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
