<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200712144339 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company ADD site_id INT DEFAULT NULL, ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql(
            'ALTER TABLE company ADD CONSTRAINT FK_4FBF094FF6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)'
        );
        $this->addSql('CREATE INDEX IDX_4FBF094FF6BD1646 ON company (site_id)');
        $this->addSql('ALTER TABLE user ADD company_id INT DEFAULT NULL');
        $this->addSql(
            'ALTER TABLE user ADD CONSTRAINT FK_8D93D649979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)'
        );
        $this->addSql('CREATE INDEX IDX_8D93D649979B1AD6 ON user (company_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company DROP FOREIGN KEY FK_4FBF094FF6BD1646');
        $this->addSql('DROP INDEX IDX_4FBF094FF6BD1646 ON company');
        $this->addSql('ALTER TABLE company DROP site_id, DROP deleted_at');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649979B1AD6');
        $this->addSql('DROP INDEX IDX_8D93D649979B1AD6 ON user');
        $this->addSql('ALTER TABLE user DROP company_id');
    }
}
