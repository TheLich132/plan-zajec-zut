<?php

namespace App\Service;

use App\Entity\Faculty;
use App\Entity\Teacher;
use App\Entity\Room;
use App\Entity\Subject;
use App\Entity\Student;
use Doctrine\ORM\EntityManagerInterface;

class RelationService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createRelationFacultyRoom(): void
    {
        $faculties = $this->entityManager->getRepository(Faculty::class)->findAll();
        $rooms = $this->entityManager->getRepository(Room::class)->findAll();

        foreach ($rooms as $room) {
            $room->setFaculty(null);
            foreach ($faculties as $faculty) {
                if (str_contains($room->getName(), $faculty->getName())) {
                    $room->setFaculty($faculty);
                    break;
                }
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

}
