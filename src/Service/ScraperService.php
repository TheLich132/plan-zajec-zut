<?php

namespace App\Service;

use App\Entity\Teacher;
use App\Entity\Room;
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
        // URL of the teachers data
        $teacher_url = 'https://plan.zut.edu.pl/schedule.php?kind=teacher&query=+';

        // Fetch the data from the URL
        $response = file_get_contents($teacher_url);
        $teachers = json_decode($response, true);

        // Get all existing teachers in the database
        $teachers_db = $this->entityManager->getRepository(Teacher::class)->findAll();

        // Remove all teachers from the database
        foreach ($teachers_db as $teacher_db) {
            $this->entityManager->remove($teacher_db);
        }

        // Persist the new teachers
        foreach ($teachers as $teacher) {
            $teacher_db = new Teacher();
            $teacher_db->setName($teacher['item']);
            $this->entityManager->persist($teacher_db);
        }

        // Flush changes to the database
        $this->entityManager->flush();
    }

    public function scrapeRooms(): void
    {
        $room_url = 'https://plan.zut.edu.pl/schedule.php?kind=room&query=+';

        $response = file_get_contents($room_url);
        $rooms = json_decode($response, true);

        $rooms_db = $this->entityManager->getRepository(Room::class)->findAll();

        foreach ($rooms_db as $room_db) {
            $this->entityManager->remove($room_db);
        }

        foreach ($rooms as $room) {
            $room_db = new Room();
            $room_db->setName($room['item']);
            $this->entityManager->persist($room_db);
        }

        $this->entityManager->flush();
    }

}
