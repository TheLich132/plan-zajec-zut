<?php

namespace App\Service;

use App\Entity\Faculty;
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

    public function isDataEmpty(string $entityClass): bool
    {
        return $this->entityManager->getRepository($entityClass)->count([]) === 0;
    }

    public function scrapeNames(string $url, string $entityClass, string $dataKey, int $batchSize = 100): void
    {
        $response = file_get_contents($url);
        $data = json_decode($response, true);

        $this->entityManager->createQuery('DELETE FROM ' . $entityClass)->execute();

        $count = 0;
        foreach ($data as $item) {
            $entity = new $entityClass();
            $entity->setName($item[$dataKey]);
            $this->entityManager->persist($entity);

            $count++;

            if ($count % $batchSize === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    public function scrapeTeachers(): void
    {
        $this->scrapeNames(
            'https://plan.zut.edu.pl/schedule.php?kind=teacher&query=+',
            Teacher::class,
            'item'
        );
    }

    public function scrapeRooms(): void
    {
        $this->scrapeNames(
            'https://plan.zut.edu.pl/schedule.php?kind=room&query=+',
            Room::class,
            'item'
        );
    }

    public function scrapeSubjects(): void
    {
        $this->scrapeNames(
            'https://plan.zut.edu.pl/schedule.php?kind=subject&query=+',
            Subject::class,
            'item'
        );
    }

    public function scrapeStudents(): void
    {
        // TODO: set those up automatically
        $start_date = '2023-10-01';
        $end_date = '2026-02-01';

        // TODO: make it not last forever
        $viable_indexes = range(50955, 50960);
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

    public function scrapeFaculties(): void
    {
        $this->entityManager->createQuery('DELETE FROM App\Entity\Faculty')->execute();
        $savedFaculties = [];

        foreach ($this->getDataInBatches("App\Entity\Subject") as $subjects) {
            foreach ($subjects as $subject) {
                $name = $this->splitSubjectString($subject->getName())[0];
                if (empty($name)) {
                    continue;
                }

                // Check if the faculty already exists
                $existingFaculty = $this->entityManager->getRepository(Faculty::class)->findOneBy(['Name' => $name]);

                if (!$existingFaculty && !in_array($name, $savedFaculties)) {
                    $faculty = new Faculty();
                    $faculty->setName($name);
                    $this->entityManager->persist($faculty);
                    $savedFaculties[] = $name;
                }
            }

            $this->entityManager->flush();
            $this->entityManager->clear();
        }
    }

    private function getDataInBatches(string $entity, int $batchSize = 100): \Generator
    {
        $offset = 0;

        while (true) {
            $query = $this->entityManager->createQuery('SELECT s FROM ' . $entity . ' s')
                ->setFirstResult($offset)
                ->setMaxResults($batchSize);

            $data = $query->getResult();

            if (count($data) === 0) {
                break;
            }

            yield $data;

            $offset += $batchSize;
        }
    }

    private function splitSubjectString(string $subject): array
    {
        $result_array = substr($subject, strrpos($subject, '(') + 1, -1);
        $result_array = explode(',', $result_array);
        $result_array = array_map('trim', $result_array);
        return $result_array;
    }

}
