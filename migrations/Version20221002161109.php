<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221002161109 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recipe DROP ingredients, DROP steps, DROP recommendations');
        $this->addSql('ALTER TABLE recommendations ADD type VARCHAR(255) NOT NULL, ADD type_id INT NOT NULL');
        $this->addSql('ALTER TABLE steps ADD recipe_id INT NOT NULL, ADD step INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recommendations DROP type, DROP type_id');
        $this->addSql('ALTER TABLE steps DROP recipe_id, DROP step');
        $this->addSql('ALTER TABLE recipe ADD ingredients VARCHAR(2550) DEFAULT NULL, ADD steps VARCHAR(2550) DEFAULT NULL, ADD recommendations VARCHAR(2550) DEFAULT NULL');
    }
}
