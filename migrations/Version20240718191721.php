<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240718191721 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE seat DROP FOREIGN KEY FK_3D5C366683A269F2');
        $this->addSql('ALTER TABLE seat ADD CONSTRAINT FK_3D5C366683A269F2 FOREIGN KEY (row_id) REFERENCES row (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE seat DROP FOREIGN KEY FK_3D5C366683A269F2');
        $this->addSql('ALTER TABLE seat ADD CONSTRAINT FK_3D5C366683A269F2 FOREIGN KEY (row_id) REFERENCES row (id)');
    }
}
