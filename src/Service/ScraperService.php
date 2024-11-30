<?php

namespace App\Service;

use App\Entity\Teacher;
use App\Entity\Room;
use App\Entity\Subject;
use App\Entity\Student;
use Doctrine\ORM\EntityManagerInterface;

class ScraperService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function scrapeTeachers(): void
    {
        $teacher_url = 'https://plan.zut.edu.pl/schedule.php?kind=teacher&query=+';

        $response = file_get_contents($teacher_url);
        $teachers = json_decode($response, true);

        $this->entityManager->createQuery('DELETE FROM App\Entity\Teacher')->execute();

        foreach ($teachers as $teacher) {
            $teacher_db = new Teacher();
            $teacher_db->setName($teacher['item']);
            $this->entityManager->persist($teacher_db);
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    public function scrapeRooms(): void
    {
        $room_url = 'https://plan.zut.edu.pl/schedule.php?kind=room&query=+';

        $response = file_get_contents($room_url);
        $rooms = json_decode($response, true);

        $this->entityManager->createQuery('DELETE FROM App\Entity\Room')->execute();

        foreach ($rooms as $room) {
            $room_db = new Room();
            $room_db->setName($room['item']);
            $this->entityManager->persist($room_db);
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    public function scrapeSubjects(): void
    {
        $subject_url = 'https://plan.zut.edu.pl/schedule.php?kind=subject&query=+';

        $response = file_get_contents($subject_url);
        $subjects = json_decode($response, true);

        $this->entityManager->createQuery('DELETE FROM App\Entity\Subject')->execute();

        foreach ($subjects as $subject) {
            $subject_db = new Subject();
            $subject_db->setName($subject['item']);
            $this->entityManager->persist($subject_db);
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    public function scrapeStudents(): void
    {
        // TODO: set those up automatically
        $start_date = '2023-10-01';
        $end_date = '2026-02-01';

        // TODO: make it not last forever
        $viable_indexes = range(50955, 50999);
        $this->entityManager->createQuery('DELETE FROM App\Entity\Student')->execute();

        foreach ($viable_indexes as $index) {
            $student_url = 'https://plan.zut.edu.pl/schedule_student.php?number=' . $index . '&start=' . $start_date . '&end=' . $end_date;
            $response = file_get_contents($student_url);

            // if the response is an empty json, skip the index
            if ($response === '[[]]') {
                continue;
            }

            $student = new Student();
            $student->setIndexNumber($index);
            $this->entityManager->persist($student);
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

}
