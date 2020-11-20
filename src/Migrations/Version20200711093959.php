<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200711093959 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE check_point (id INT AUTO_INCREMENT NOT NULL, site_id INT NOT NULL, name VARCHAR(255) NOT NULL, asset_id VARCHAR(255) NOT NULL, location_information LONGTEXT DEFAULT NULL, latitude NUMERIC(10, 6) DEFAULT NULL, longitude NUMERIC(10, 6) DEFAULT NULL, active TINYINT(1) NOT NULL, deletedAt DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_2DFFC0E65DA1941 (asset_id), INDEX IDX_2DFFC0E6F6BD1646 (site_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE checkpoint_interaction (id INT AUTO_INCREMENT NOT NULL, checkpoint_id INT NOT NULL, user_id INT NOT NULL, shift_id INT NOT NULL, submitted DATETIME NOT NULL, live TINYINT(1) NOT NULL, deletedAt DATETIME DEFAULT NULL, INDEX IDX_8BAE789DF27C615F (checkpoint_id), INDEX IDX_8BAE789DA76ED395 (user_id), INDEX IDX_8BAE789DBB70BC0E (shift_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE ext_log_entries (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(8) NOT NULL, logged_at DATETIME NOT NULL, object_id VARCHAR(64) DEFAULT NULL, object_class VARCHAR(255) NOT NULL, version INT NOT NULL, data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', username VARCHAR(255) DEFAULT NULL, INDEX log_class_lookup_idx (object_class), INDEX log_date_lookup_idx (logged_at), INDEX log_user_lookup_idx (username), INDEX log_version_lookup_idx (object_id, object_class, version), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB ROW_FORMAT = DYNAMIC'
        );
        $this->addSql(
            'CREATE TABLE guard_shift (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, admin_id INT NOT NULL, site_id INT NOT NULL, shift_start DATETIME NOT NULL, shift_end DATETIME NOT NULL, actual_shift_start DATETIME DEFAULT NULL, actual_shift_end DATETIME DEFAULT NULL, deletedAt DATETIME DEFAULT NULL, INDEX IDX_466D610DA76ED395 (user_id), INDEX IDX_466D610D642B8210 (admin_id), INDEX IDX_466D610DF6BD1646 (site_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE perimeter (id INT AUTO_INCREMENT NOT NULL, site_id INT DEFAULT NULL, ref_number INT NOT NULL, latitude NUMERIC(10, 6) NOT NULL, longitude NUMERIC(10, 6) NOT NULL, deletedAt DATETIME DEFAULT NULL, INDEX IDX_428E1D87F6BD1646 (site_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE site (id INT AUTO_INCREMENT NOT NULL, description LONGTEXT DEFAULT NULL, creation_date DATETIME NOT NULL, active TINYINT(1) NOT NULL, name VARCHAR(255) NOT NULL, tap_frequency INT DEFAULT NULL, deletedAt DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_694309E45E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, last_logged_in DATETIME NOT NULL, qualifications LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', registration_date DATETIME NOT NULL, active TINYINT(1) NOT NULL, deletedAt DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'ALTER TABLE check_point ADD CONSTRAINT FK_2DFFC0E6F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)'
        );
        $this->addSql(
            'ALTER TABLE checkpoint_interaction ADD CONSTRAINT FK_8BAE789DF27C615F FOREIGN KEY (checkpoint_id) REFERENCES check_point (id)'
        );
        $this->addSql(
            'ALTER TABLE checkpoint_interaction ADD CONSTRAINT FK_8BAE789DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)'
        );
        $this->addSql(
            'ALTER TABLE checkpoint_interaction ADD CONSTRAINT FK_8BAE789DBB70BC0E FOREIGN KEY (shift_id) REFERENCES guard_shift (id)'
        );
        $this->addSql(
            'ALTER TABLE guard_shift ADD CONSTRAINT FK_466D610DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)'
        );
        $this->addSql(
            'ALTER TABLE guard_shift ADD CONSTRAINT FK_466D610D642B8210 FOREIGN KEY (admin_id) REFERENCES user (id)'
        );
        $this->addSql(
            'ALTER TABLE guard_shift ADD CONSTRAINT FK_466D610DF6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)'
        );
        $this->addSql(
            'ALTER TABLE perimeter ADD CONSTRAINT FK_428E1D87F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE checkpoint_interaction DROP FOREIGN KEY FK_8BAE789DF27C615F');
        $this->addSql('ALTER TABLE checkpoint_interaction DROP FOREIGN KEY FK_8BAE789DBB70BC0E');
        $this->addSql('ALTER TABLE check_point DROP FOREIGN KEY FK_2DFFC0E6F6BD1646');
        $this->addSql('ALTER TABLE guard_shift DROP FOREIGN KEY FK_466D610DF6BD1646');
        $this->addSql('ALTER TABLE perimeter DROP FOREIGN KEY FK_428E1D87F6BD1646');
        $this->addSql('ALTER TABLE checkpoint_interaction DROP FOREIGN KEY FK_8BAE789DA76ED395');
        $this->addSql('ALTER TABLE guard_shift DROP FOREIGN KEY FK_466D610DA76ED395');
        $this->addSql('ALTER TABLE guard_shift DROP FOREIGN KEY FK_466D610D642B8210');
        $this->addSql('DROP TABLE check_point');
        $this->addSql('DROP TABLE checkpoint_interaction');
        $this->addSql('DROP TABLE ext_log_entries');
        $this->addSql('DROP TABLE guard_shift');
        $this->addSql('DROP TABLE perimeter');
        $this->addSql('DROP TABLE site');
        $this->addSql('DROP TABLE user');
    }
}
