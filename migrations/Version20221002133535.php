<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221002133535 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recipe CHANGE status status VARCHAR(30) DEFAULT NULL, CHANGE visibility visibility VARCHAR(30) DEFAULT NULL, CHANGE name name VARCHAR(100) NOT NULL, CHANGE description description VARCHAR(2550) DEFAULT NULL, CHANGE ingredients ingredients VARCHAR(2550) DEFAULT NULL, CHANGE steps steps VARCHAR(2550) DEFAULT NULL, CHANGE recommendations recommendations VARCHAR(2550) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recipe CHANGE status status VARCHAR(255) DEFAULT NULL, CHANGE visibility visibility VARCHAR(255) DEFAULT NULL, CHANGE name name VARCHAR(255) NOT NULL, CHANGE description description VARCHAR(1000) DEFAULT NULL, CHANGE ingredients ingredients VARCHAR(255) DEFAULT NULL, CHANGE steps steps JSON DEFAULT NULL, CHANGE recommendations recommendations JSON DEFAULT NULL');
    }
}
