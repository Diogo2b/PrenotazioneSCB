<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240724090258 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payment_ticket DROP FOREIGN KEY FK_B29836354C3A3BB');
        $this->addSql('ALTER TABLE payment_ticket DROP FOREIGN KEY FK_B2983635700047D2');
        $this->addSql('DROP TABLE payment_ticket');
        $this->addSql('ALTER TABLE ticket ADD payment_id INT NOT NULL');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA34C3A3BB FOREIGN KEY (payment_id) REFERENCES payment (id)');
        $this->addSql('CREATE INDEX IDX_97A0ADA34C3A3BB ON ticket (payment_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE payment_ticket (id INT AUTO_INCREMENT NOT NULL, payment_id INT NOT NULL, ticket_id INT NOT NULL, INDEX IDX_B29836354C3A3BB (payment_id), INDEX IDX_B2983635700047D2 (ticket_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE payment_ticket ADD CONSTRAINT FK_B29836354C3A3BB FOREIGN KEY (payment_id) REFERENCES payment (id)');
        $this->addSql('ALTER TABLE payment_ticket ADD CONSTRAINT FK_B2983635700047D2 FOREIGN KEY (ticket_id) REFERENCES ticket (id)');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA34C3A3BB');
        $this->addSql('DROP INDEX IDX_97A0ADA34C3A3BB ON ticket');
        $this->addSql('ALTER TABLE ticket DROP payment_id');
    }
}
