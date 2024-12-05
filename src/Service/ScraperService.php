<?php

namespace App\Service;

use App\Entity\Faculty;
use App\Entity\Group;
use App\Entity\Lesson;
use App\Entity\Teacher;
use App\Entity\Room;
use App\Entity\Subject;
use App\Entity\Student;
use Doctrine\ORM\EntityManagerInterface;

class ScraperService
{
    private EntityManagerInterface $entityManager;
    private RelationService $relationService;

    // TODO: set the start and end date dynamically
    private string $startDate = '2024-12-02';
    private string $endDate = '2024-12-08';

    public function __construct(EntityManagerInterface $entityManager, RelationService $relationService)
    {
        $this->entityManager = $entityManager;
        $this->relationService = $relationService;
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
        $lessons = $this->entityManager->getRepository(Lesson::class)->findAll();
        // TODO: make it not last forever
        $viableIndexes = range(50955, 50960);
        $this->entityManager->createQuery('DELETE FROM App\Entity\Student')->execute();
        $this->entityManager->getConnection()->executeStatement('DELETE FROM group_student');

        foreach ($viableIndexes as $index) {
            $studentUrl = 'https://plan.zut.edu.pl/schedule_student.php?number=' . $index . '&start=' . $this->startDate . '&end=' . $this->endDate;
            $response = file_get_contents($studentUrl);

            if ($response === '[[]]') {
                continue;
            }

            $data = json_decode($response, true);

            $student = new Student();
            $student->setIndexNumber($index);
            foreach ($data as $item) {
                if (empty($item['worker'])) {
                    continue;
                }
                foreach ($lessons as $lesson) {
                    // allign to this format 2024-12-03T10:15:00
                    if ($lesson->getTeacher()->getName() === $item['worker']
                        && $lesson->getStart()->format('Y-m-d\TH:i:s') === $item['start']
                        && $lesson->getFinish()->format('Y-m-d\TH:i:s') === $item['end']
                        && $lesson->getRoom() !== null
                        && $lesson->getRoom()->getName() === $item['room']) {
                        $student->addGroup($lesson->getStudentGroup());
                    }
                }
            }
            $this->entityManager->persist($student);
            echo 'Student ' . $index . ' scraped successfully.' . PHP_EOL;
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    public function scrapeFaculties(): void
    {
        if ($this->isDataEmpty(Subject::class)) {
            return;
        }

        $this->entityManager->createQuery('DELETE FROM App\Entity\Faculty')->execute();
        $savedFaculties = ['IiJM', 'AOJ', 'SWFiS'];
        // Manual addition of faculties
        foreach ($savedFaculties as $facultyName) {
            $faculty = new Faculty();
            $faculty->setName($facultyName);
            $this->entityManager->persist($faculty);
        }


        foreach ($this->getDataInBatches("App\Entity\Subject") as $subjects) {
            foreach ($subjects as $subject) {
                $name = $this->splitSubjectString($subject->getName())[0];
                if (empty($name)) {
                    continue;
                }

                if ($name === 'WBiIS') {
                    $name = 'WBiIŚ';
                }

                if ($name === 'WTiICh') {
                    $name = 'WTiICH';
                }

                // Check if the faculty already exists
                $existingFaculty = $this->entityManager->getRepository(Faculty::class)->findOneBy(['name' => $name]);

                if (!$existingFaculty && !in_array($name, $savedFaculties)) {
                    $faculty = new Faculty();
                    $faculty->setName($name);
                    $this->entityManager->persist($faculty);
                    $savedFaculties[] = $name;
                }
            }

            $this->entityManager->flush();
            $this->entityManager->clear();

            $this->relationService->createRelationFacultyRoom();
        }
    }

    public function scrapeLessons(): void
    {
        if ($this->isDataEmpty(Teacher::class)) {
            return;
        }

        $this->entityManager->createQuery('DELETE FROM App\Entity\Lesson')->execute();

//        $teachers = $this->entityManager->getRepository(Teacher::class)->findAll();
        $subjects = $this->entityManager->getRepository(Subject::class)->findAll();
        $karczmarczyk = new Teacher();
        $karczmarczyk->setName('Karczmarczyk Artur');
        $this->entityManager->persist($karczmarczyk);
        $this->entityManager->flush();

        $teachers = [$karczmarczyk];

        $counter = 0;
        foreach ($teachers as $teacher) {
            $name = $teacher->getName();
            $name = str_replace(' ', '%20', $name);
            $url = 'https://plan.zut.edu.pl/schedule_student.php?teacher=' . $name . '&start=' . $this->startDate . '&end=' . $this->endDate;
            $response = file_get_contents($url);
            $fullData = json_decode($response, true);
            foreach ($fullData as $data) {
                if (empty($data['title'])) {
                    continue;
                }
                $lesson = new Lesson();
                $lesson->setName($data['title']);
                $lesson->setFormLesson($data['lesson_form']);
                $lesson->setHours($data['hours']);
                $lesson->setStart(new \DateTime($data['start']));
                $lesson->setFinish(new \DateTime($data['end']));
                $lesson->setTeacher($teacher);
                $room = $this->entityManager->getRepository(Room::class)->findOneBy(['name' => $data['room']]);
                $lesson->setRoom($room);
                // TODO: assign correct subject based on faculty, study-form etc
                foreach ($subjects as $subject) {
                    if (stripos($subject->getName(), $data['subject']) !== false) {
                        $lesson->setSubject($subject);
                        break;
                    }
                }
                $group = $this->entityManager->getRepository(Group::class)->findOneBy(['name' => $data['group_name']]);
                if (!$group && !empty($data['group_name'])) {
                    $group = new Group();
                    $group->setName($data['group_name']);
                    $this->entityManager->persist($group);
                }
                $lesson->setStudentGroup($group);
                $this->entityManager->persist($lesson);
            }
            $counter++;
            if ($counter >= 10) {
                break;
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
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
        $resultArray = substr($subject, strrpos($subject, '(') + 1, -1);
        $resultArray = explode(',', $resultArray);
        $resultArray = array_map('trim', $resultArray);
        return $resultArray;
    }

}
