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
    public function filter(string              $faculty = '',
                           string              $teacher = '',
                           string              $room = '',
                           string              $subject = '',
                           string              $group = '',
                           string              $student = '',
                           bool                $isStationary = null,
                           string              $form = '',
                           DateTimeInterface $start = new \DateTime('1993-01-01 00:00:00'),
                           DateTimeInterface $finish = new \DateTime('2033-12-31 23:59:59')): array
    {

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
            $qb->leftJoin('l.studentGroup', 'g')
                ->leftJoin('g.students', 'st')
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


}
