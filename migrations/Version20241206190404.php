<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241206190404 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE faculty (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_179660435E237E06 ON faculty (name)');
        $this->addSql('CREATE TABLE "group" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE group_student (group_id INTEGER NOT NULL, student_id INTEGER NOT NULL, PRIMARY KEY(group_id, student_id), CONSTRAINT FK_3123FB3FFE54D947 FOREIGN KEY (group_id) REFERENCES "group" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3123FB3FCB944F1A FOREIGN KEY (student_id) REFERENCES student (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_3123FB3FFE54D947 ON group_student (group_id)');
        $this->addSql('CREATE INDEX IDX_3123FB3FCB944F1A ON group_student (student_id)');
        $this->addSql('CREATE TABLE lesson (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, subject_id INTEGER DEFAULT NULL, teacher_id INTEGER DEFAULT NULL, room_id INTEGER DEFAULT NULL, student_group_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, form_lesson VARCHAR(255), hours INTEGER, start DATETIME, finish DATETIME, CONSTRAINT FK_F87474F323EDC87 FOREIGN KEY (subject_id) REFERENCES subject (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_F87474F341807E1D FOREIGN KEY (teacher_id) REFERENCES teacher (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_F87474F354177093 FOREIGN KEY (room_id) REFERENCES room (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_F87474F34DDF95DC FOREIGN KEY (student_group_id) REFERENCES "group" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_F87474F323EDC87 ON lesson (subject_id)');
        $this->addSql('CREATE INDEX IDX_F87474F341807E1D ON lesson (teacher_id)');
        $this->addSql('CREATE INDEX IDX_F87474F354177093 ON lesson (room_id)');
        $this->addSql('CREATE INDEX IDX_F87474F34DDF95DC ON lesson (student_group_id)');
        $this->addSql('CREATE TABLE major (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, faculty_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, CONSTRAINT FK_3D34FD09680CAB68 FOREIGN KEY (faculty_id) REFERENCES faculty (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_3D34FD09680CAB68 ON major (faculty_id)');
        $this->addSql('CREATE TABLE room (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, faculty_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, CONSTRAINT FK_729F519B680CAB68 FOREIGN KEY (faculty_id) REFERENCES faculty (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_729F519B680CAB68 ON room (faculty_id)');
        $this->addSql('CREATE TABLE student (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, index_number INTEGER NOT NULL)');
        $this->addSql('CREATE TABLE subject (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, major_id INTEGER DEFAULT NULL, faculty_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, degree VARCHAR(255) DEFAULT NULL, is_stationary BOOLEAN DEFAULT NULL, CONSTRAINT FK_FBCE3E7AE93695C7 FOREIGN KEY (major_id) REFERENCES major (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_FBCE3E7A680CAB68 FOREIGN KEY (faculty_id) REFERENCES faculty (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_FBCE3E7AE93695C7 ON subject (major_id)');
        $this->addSql('CREATE INDEX IDX_FBCE3E7A680CAB68 ON subject (faculty_id)');
        $this->addSql('CREATE TABLE teacher (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE messenger_messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, body CLOB NOT NULL, headers CLOB NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , available_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , delivered_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        )');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE faculty');
        $this->addSql('DROP TABLE "group"');
        $this->addSql('DROP TABLE group_student');
        $this->addSql('DROP TABLE lesson');
        $this->addSql('DROP TABLE major');
        $this->addSql('DROP TABLE room');
        $this->addSql('DROP TABLE student');
        $this->addSql('DROP TABLE subject');
        $this->addSql('DROP TABLE teacher');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
