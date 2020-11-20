<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200831194530 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'ALTER TABLE `user` ADD COLUMN `default_pay_rate` INT NOT NULL DEFAULT 30 AFTER `qualifications`;'
        );
        $this->addSql(
            'ALTER TABLE `site` ADD COLUMN `day_rate` INT NULL AFTER `tap_frequency`;'
        );
        $this->addSql(
            'ALTER TABLE `site` ADD COLUMN `night_rate` INT NULL AFTER `day_rate`;'
        );
        $this->addSql(
            "ALTER TABLE `site` ADD COLUMN `day_shift_start_time` VARCHAR(10) NOT NULL DEFAULT '08:00' AFTER `night_rate`;"
        );
        $this->addSql(
            "ALTER TABLE `site` ADD COLUMN `night_shift_start_time` VARCHAR(10) NOT NULL DEFAULT '20:00' AFTER `day_shift_start_time`;"
        );
        $this->addSql(
            'ALTER TABLE `site` ADD COLUMN `late_limit` INT NOT NULL DEFAULT 15 AFTER `night_shift_start_time`;'
        );
        $this->addSql(
            'ALTER TABLE `guard_shift` ADD COLUMN `approved` TINYINT NOT NULL DEFAULT 0 AFTER `actual_shift_end`;'
        );
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `user` DROP COLUMN `default_pay_rate`;');
        $this->addSql('ALTER TABLE `site` DROP COLUMN `day_rate`;');
        $this->addSql('ALTER TABLE `site` DROP COLUMN `night_rate`;');
        $this->addSql('ALTER TABLE `site` DROP COLUMN `day_shift_start_time`;');
        $this->addSql('ALTER TABLE `site` DROP COLUMN `night_shift_start_time`;');
        $this->addSql('ALTER TABLE `site` DROP COLUMN `late_limit`;');
        $this->addSql('ALTER TABLE `guard_shift` DROP COLUMN `approved`;');
    }
}
