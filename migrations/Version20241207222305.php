<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241207222305 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__faculty AS SELECT id, name FROM faculty');
        $this->addSql('DROP TABLE faculty');
        $this->addSql('CREATE TABLE faculty (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL)');
        $this->addSql('INSERT INTO faculty (id, name) SELECT id, name FROM __temp__faculty');
        $this->addSql('DROP TABLE __temp__faculty');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_179660435E237E06 ON faculty (name)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__group AS SELECT id, name FROM "group"');
        $this->addSql('DROP TABLE "group"');
        $this->addSql('CREATE TABLE "group" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL)');
        $this->addSql('INSERT INTO "group" (id, name) SELECT id, name FROM __temp__group');
        $this->addSql('DROP TABLE __temp__group');
        $this->addSql('CREATE TEMPORARY TABLE __temp__lesson AS SELECT id, subject_id, teacher_id, room_id, student_group_id, name, form_lesson, hours, start, finish FROM lesson');
        $this->addSql('DROP TABLE lesson');
        $this->addSql('CREATE TABLE lesson (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, subject_id INTEGER DEFAULT NULL, teacher_id INTEGER DEFAULT NULL, room_id INTEGER DEFAULT NULL, student_group_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, form_lesson VARCHAR(255) DEFAULT NULL, hours DOUBLE PRECISION DEFAULT NULL, start DATETIME NOT NULL, finish DATETIME NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CONSTRAINT FK_F87474F323EDC87 FOREIGN KEY (subject_id) REFERENCES subject (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_F87474F341807E1D FOREIGN KEY (teacher_id) REFERENCES teacher (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_F87474F354177093 FOREIGN KEY (room_id) REFERENCES room (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_F87474F34DDF95DC FOREIGN KEY (student_group_id) REFERENCES "group" (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO lesson (id, subject_id, teacher_id, room_id, student_group_id, name, form_lesson, hours, start, finish) SELECT id, subject_id, teacher_id, room_id, student_group_id, name, form_lesson, hours, start, finish FROM __temp__lesson');
        $this->addSql('DROP TABLE __temp__lesson');
        $this->addSql('CREATE INDEX IDX_F87474F34DDF95DC ON lesson (student_group_id)');
        $this->addSql('CREATE INDEX IDX_F87474F354177093 ON lesson (room_id)');
        $this->addSql('CREATE INDEX IDX_F87474F341807E1D ON lesson (teacher_id)');
        $this->addSql('CREATE INDEX IDX_F87474F323EDC87 ON lesson (subject_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__major AS SELECT id, faculty_id, name FROM major');
        $this->addSql('DROP TABLE major');
        $this->addSql('CREATE TABLE major (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, faculty_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CONSTRAINT FK_3D34FD09680CAB68 FOREIGN KEY (faculty_id) REFERENCES faculty (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO major (id, faculty_id, name) SELECT id, faculty_id, name FROM __temp__major');
        $this->addSql('DROP TABLE __temp__major');
        $this->addSql('CREATE INDEX IDX_3D34FD09680CAB68 ON major (faculty_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__room AS SELECT id, faculty_id, name FROM room');
        $this->addSql('DROP TABLE room');
        $this->addSql('CREATE TABLE room (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, faculty_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CONSTRAINT FK_729F519B680CAB68 FOREIGN KEY (faculty_id) REFERENCES faculty (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO room (id, faculty_id, name) SELECT id, faculty_id, name FROM __temp__room');
        $this->addSql('DROP TABLE __temp__room');
        $this->addSql('CREATE INDEX IDX_729F519B680CAB68 ON room (faculty_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__student AS SELECT id, index_number FROM student');
        $this->addSql('DROP TABLE student');
        $this->addSql('CREATE TABLE student (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, index_number INTEGER NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL)');
        $this->addSql('INSERT INTO student (id, index_number) SELECT id, index_number FROM __temp__student');
        $this->addSql('DROP TABLE __temp__student');
        $this->addSql('CREATE TEMPORARY TABLE __temp__subject AS SELECT id, major_id, faculty_id, name, degree, is_stationary FROM subject');
        $this->addSql('DROP TABLE subject');
        $this->addSql('CREATE TABLE subject (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, major_id INTEGER DEFAULT NULL, faculty_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, degree VARCHAR(255) DEFAULT NULL, is_stationary BOOLEAN DEFAULT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CONSTRAINT FK_FBCE3E7AE93695C7 FOREIGN KEY (major_id) REFERENCES major (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_FBCE3E7A680CAB68 FOREIGN KEY (faculty_id) REFERENCES faculty (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO subject (id, major_id, faculty_id, name, degree, is_stationary) SELECT id, major_id, faculty_id, name, degree, is_stationary FROM __temp__subject');
        $this->addSql('DROP TABLE __temp__subject');
        $this->addSql('CREATE INDEX IDX_FBCE3E7A680CAB68 ON subject (faculty_id)');
        $this->addSql('CREATE INDEX IDX_FBCE3E7AE93695C7 ON subject (major_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__teacher AS SELECT id, name FROM teacher');
        $this->addSql('DROP TABLE teacher');
        $this->addSql('CREATE TABLE teacher (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL)');
        $this->addSql('INSERT INTO teacher (id, name) SELECT id, name FROM __temp__teacher');
        $this->addSql('DROP TABLE __temp__teacher');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__faculty as SELECT id, name FROM faculty');
        $this->addSql('DROP TABLE faculty');
        $this->addSql('CREATE TABLE faculty(id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO faculty(id, name) SELECT id, name FROM __temp__faculty');
        $this->addSql('DROP TABLE __temp__faculty');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_179660435E237E06 ON faculty(name)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__group as SELECT id, name FROM "group"');
        $this->addSql('DROP TABLE "group"');
        $this->addSql('CREATE TABLE "group" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO "group" (id, name) SELECT id, name FROM __temp__group');
        $this->addSql('DROP TABLE __temp__group');
        $this->addSql('CREATE TEMPORARY TABLE __temp__lesson as SELECT id, subject_id, teacher_id, room_id, student_group_id, name, form_lesson, hours, start, finish FROM lesson');
        $this->addSql('DROP TABLE lesson');
        $this->addSql('CREATE TABLE lesson(id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, subject_id INTEGER default NULL, teacher_id INTEGER default NULL, room_id INTEGER default NULL, student_group_id INTEGER default NULL, name VARCHAR(255) NOT NULL, form_lesson VARCHAR(255) default NULL, hours DOUBLE PRECISION default NULL, start DATETIME NOT NULL, finish DATETIME NOT NULL, CONSTRAINT FK_F87474F323EDC87 FOREIGN KEY(subject_id) REFERENCES subject(id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_F87474F341807E1D FOREIGN KEY(teacher_id) REFERENCES teacher(id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_F87474F354177093 FOREIGN KEY(room_id) REFERENCES room(id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_F87474F34DDF95DC FOREIGN KEY(student_group_id) REFERENCES "group" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO lesson(id, subject_id, teacher_id, room_id, student_group_id, name, form_lesson, hours, start, finish) SELECT id, subject_id, teacher_id, room_id, student_group_id, name, form_lesson, hours, start, finish FROM __temp__lesson');
        $this->addSql('DROP TABLE __temp__lesson');
        $this->addSql('CREATE INDEX IDX_F87474F323EDC87 ON lesson(subject_id)');
        $this->addSql('CREATE INDEX IDX_F87474F341807E1D ON lesson(teacher_id)');
        $this->addSql('CREATE INDEX IDX_F87474F354177093 ON lesson(room_id)');
        $this->addSql('CREATE INDEX IDX_F87474F34DDF95DC ON lesson(student_group_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__major as SELECT id, faculty_id, name FROM major');
        $this->addSql('DROP TABLE major');
        $this->addSql('CREATE TABLE major(id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, faculty_id INTEGER default NULL, name VARCHAR(255) NOT NULL, CONSTRAINT FK_3D34FD09680CAB68 FOREIGN KEY(faculty_id) REFERENCES faculty(id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO major(id, faculty_id, name) SELECT id, faculty_id, name FROM __temp__major');
        $this->addSql('DROP TABLE __temp__major');
        $this->addSql('CREATE INDEX IDX_3D34FD09680CAB68 ON major(faculty_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__room as SELECT id, faculty_id, name FROM room');
        $this->addSql('DROP TABLE room');
        $this->addSql('CREATE TABLE room(id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, faculty_id INTEGER default NULL, name VARCHAR(255) NOT NULL, CONSTRAINT FK_729F519B680CAB68 FOREIGN KEY(faculty_id) REFERENCES faculty(id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO room(id, faculty_id, name) SELECT id, faculty_id, name FROM __temp__room');
        $this->addSql('DROP TABLE __temp__room');
        $this->addSql('CREATE INDEX IDX_729F519B680CAB68 ON room(faculty_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__student as SELECT id, index_number FROM student');
        $this->addSql('DROP TABLE student');
        $this->addSql('CREATE TABLE student(id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, index_number INTEGER NOT NULL)');
        $this->addSql('INSERT INTO student(id, index_number) SELECT id, index_number FROM __temp__student');
        $this->addSql('DROP TABLE __temp__student');
        $this->addSql('CREATE TEMPORARY TABLE __temp__subject as SELECT id, major_id, faculty_id, name, degree, is_stationary FROM subject');
        $this->addSql('DROP TABLE subject');
        $this->addSql('CREATE TABLE subject(id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, major_id INTEGER default NULL, faculty_id INTEGER default NULL, name VARCHAR(255) NOT NULL, degree VARCHAR(255) default NULL, is_stationary BOOLEAN default NULL, CONSTRAINT FK_FBCE3E7AE93695C7 FOREIGN KEY(major_id) REFERENCES major(id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_FBCE3E7A680CAB68 FOREIGN KEY(faculty_id) REFERENCES faculty(id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO subject(id, major_id, faculty_id, name, degree, is_stationary) SELECT id, major_id, faculty_id, name, degree, is_stationary FROM __temp__subject');
        $this->addSql('DROP TABLE __temp__subject');
        $this->addSql('CREATE INDEX IDX_FBCE3E7AE93695C7 ON subject(major_id)');
        $this->addSql('CREATE INDEX IDX_FBCE3E7A680CAB68 ON subject(faculty_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__teacher as SELECT id, name FROM teacher');
        $this->addSql('DROP TABLE teacher');
        $this->addSql('CREATE TABLE teacher(id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO teacher(id, name) SELECT id, name FROM __temp__teacher');
        $this->addSql('DROP TABLE __temp__teacher');
    }
}
