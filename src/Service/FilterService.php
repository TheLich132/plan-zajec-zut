<?php

namespace App\Service;

use App\Entity\Faculty;
use App\Entity\Teacher;
use App\Entity\Room;
use App\Entity\Subject;
use App\Entity\Student;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;

class FilterService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $faculty
     * @param string $teacher
     * @param string $room
     * @param string $subject
     * @param string $group
     * @param string $student
     * @param bool $isStationary
     * @param string $form
     * @param DateTimeInterface $start
     * @param DateTimeInterface $finish
     * @return array
     */
    public function filter(string            $faculty = '',
                           string            $teacher = '',
                           string            $room = '',
                           string            $subject = '',
                           string            $group = '',
                           string            $student = '',
                           bool              $isStationary = null,
                           string            $form = '',
                           DateTimeInterface $start = new \DateTime('1993-01-01 00:00:00'),
                           DateTimeInterface $finish = new \DateTime('2033-12-31 23:59:59')): array
    {
        $this->deleteOldData();

        foreach (['faculty', 'teacher', 'room', 'subject', 'group', 'student', 'form'] as $param) {
            $$param = trim($$param);
        }

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('l')
            ->from('App\Entity\Lesson', 'l');

        if (!empty($teacher)) {
            $qb->leftJoin('l.teacher', 't')
                ->andWhere('t.name = :teacher')
                ->setParameter('teacher', $teacher);
        }

        $qb->andwhere('l.start >= :start')
            ->andWhere('l.finish <= :finish')
            ->setParameter('start', $start)
            ->setParameter('finish', $finish);

        if (!empty($room)) {
            $qb->leftJoin('l.room', 'r')
                ->andWhere('r.name = :room')
                ->setParameter('room', $room);
        }

        if (!empty($group)) {
            $qb->leftJoin('l.studentGroup', 'g')
                ->andWhere('g.name = :group')
                ->setParameter('group', $group);
        }

        if (!empty($subject)) {
            $qb->leftJoin('l.subject', 's')
                ->where('s.name = :subject')
                ->setParameter('subject', $subject);
        }

        if (!empty($student)) {
            $qb->leftJoin('l.students', 'st')
                ->andWhere('st.indexNumber = :student')
                ->setParameter('student', $student);
        }

        if (!empty($faculty)) {
            $qb->leftJoin('l.room', 'ro')
                ->leftJoin('ro.faculty', 'f')
                ->andWhere('f.name = :faculty')
                ->setParameter('faculty', $faculty);
        }

        if ($isStationary !== null) {
            $qb->leftJoin('l.subject', 'su')
                ->andWhere('su.isStationary = :isStationary')
                ->setParameter('isStationary', $isStationary);
        }

        if (!empty($form)) {
            $qb->andWhere('l.formLesson = :form')
                ->setParameter('form', $form);
        }


        return $qb->getQuery()->getResult();
    }

    // Returns all names from the table that are similar to the provided string
    // Example usage: filterService->suggest(Teacher::class, "Kowal");
    public function suggest(string $entity, string $name): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('e.name')
            ->from($entity, 'e')
            ->where('e.name LIKE :name')
            ->setParameter('name', '%' . $name . '%');

        return array_column($qb->getQuery()->getResult(), 'name');
    }

    // Deletes rows from all tables that are older than a year
    public function deleteOldData(): void
    {
        $this->entityManager->createQuery('DELETE FROM App\Entity\Lesson l WHERE l.createdAt < :date')
            ->setParameter('date', new \DateTime('-1 year'))
            ->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Group g WHERE g.createdAt < :date')
            ->setParameter('date', new \DateTime('-1 year'))
            ->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Student s WHERE s.createdAt < :date')
            ->setParameter('date', new \DateTime('-1 year'))
            ->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Room r WHERE r.createdAt < :date')
            ->setParameter('date', new \DateTime('-1 year'))
            ->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Teacher t WHERE t.createdAt < :date')
            ->setParameter('date', new \DateTime('-1 year'))
            ->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Subject s WHERE s.createdAt < :date')
            ->setParameter('date', new \DateTime('-1 year'))
            ->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Faculty f WHERE f.createdAt < :date')
            ->setParameter('date', new \DateTime('-1 year'))
            ->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Major m WHERE m.createdAt < :date')
            ->setParameter('date', new \DateTime('-1 year'))
            ->execute();
        // Killing orphans for the greater good of the database
        $this->entityManager->getConnection()->executeStatement('DELETE FROM group_student WHERE group_id NOT IN (SELECT id FROM `group`) OR student_id NOT IN (SELECT id FROM student)');
        $this->entityManager->getConnection()->executeStatement('DELETE FROM student_lesson WHERE student_id NOT IN (SELECT id FROM student) OR lesson_id NOT IN (SELECT id FROM lesson)');
    }


}
