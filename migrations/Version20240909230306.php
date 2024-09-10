<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240909230306 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE clicks DROP FOREIGN KEY FK_20DA190181CFDAE7');
        $this->addSql('ALTER TABLE clicks ADD CONSTRAINT FK_20DA190181CFDAE7 FOREIGN KEY (url_id) REFERENCES urls (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE clicks DROP FOREIGN KEY FK_20DA190181CFDAE7');
        $this->addSql('ALTER TABLE clicks ADD CONSTRAINT FK_20DA190181CFDAE7 FOREIGN KEY (url_id) REFERENCES urls (id)');
    }
}
