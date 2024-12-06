<?php

namespace App\Service;

use App\Entity\Faculty;
use App\Entity\Teacher;
use App\Entity\Room;
use App\Entity\Subject;
use App\Entity\Student;
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
     * @return array
     */
    public function filter(string $faculty = '', string $teacher = '', string $room = '', string $subject = '', string $group = '', string $student = ''): array
    {
        echo "Input: Faculty: $faculty, Teacher: $teacher, Room: $room, Subject: $subject, Group: $group, Student: $student\n";

        // trim the input
        foreach (['faculty', 'teacher', 'room', 'subject', 'group', 'student'] as $param) {
            $$param = trim($$param);
        }

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('l')
            ->from('App\Entity\Lesson', 'l');

        if (!empty($room)){
            $qb->leftJoin('l.room', 'r')
                ->andWhere('r.name = :room')
                ->setParameter('room', $room);
        }

        if (!empty($teacher)){
            $qb->leftJoin('l.teacher', 't')
                ->andWhere('t.name = :teacher')
                ->setParameter('teacher', $teacher);
        }

        if (!empty($group)){
            $qb->leftJoin('l.studentGroup', 'g')
                ->andWhere('g.name = :group')
                ->setParameter('group', $group);
        }

        if ($subject){
            $qb->leftJoin('l.subject', 's')
                ->where('s.name = :subject')
                ->setParameter('subject', $subject);
        }

//        if ($faculty){
//            $qb->leftJoin('l.faculty', 'f')
//                ->where('f.name = :faculty')
//                ->setParameter('faculty', $faculty);
//        }





        return $qb->getQuery()->getResult();
    }


}
