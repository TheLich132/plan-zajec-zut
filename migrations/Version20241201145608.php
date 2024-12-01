<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241201145608 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE "group" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE group_student (group_id INTEGER NOT NULL, student_id INTEGER NOT NULL, PRIMARY KEY(group_id, student_id), CONSTRAINT FK_3123FB3FFE54D947 FOREIGN KEY (group_id) REFERENCES "group" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3123FB3FCB944F1A FOREIGN KEY (student_id) REFERENCES student (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_3123FB3FFE54D947 ON group_student (group_id)');
        $this->addSql('CREATE INDEX IDX_3123FB3FCB944F1A ON group_student (student_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__faculty AS SELECT id, name FROM faculty');
        $this->addSql('DROP TABLE faculty');
        $this->addSql('CREATE TABLE faculty (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO faculty (id, name) SELECT id, name FROM __temp__faculty');
        $this->addSql('DROP TABLE __temp__faculty');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_179660435E237E06 ON faculty (name)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE "group"');
        $this->addSql('DROP TABLE group_student');
        $this->addSql('CREATE TEMPORARY TABLE __temp__faculty AS SELECT id, name FROM faculty');
        $this->addSql('DROP TABLE faculty');
        $this->addSql('CREATE TABLE faculty (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO faculty (id, name) SELECT id, name FROM __temp__faculty');
        $this->addSql('DROP TABLE __temp__faculty');
    }
}
