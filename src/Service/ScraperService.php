<?php

namespace App\Service;

use App\Entity\Faculty;
use App\Entity\Group;
use App\Entity\Lesson;
use App\Entity\Major;
use App\Entity\Teacher;
use App\Entity\Room;
use App\Entity\Subject;
use App\Entity\Student;
use App\Enum\Degree;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;

class ScraperService
{
    private EntityManagerInterface $entityManager;

    private SemesterService $semesterService;

    private string $startDate;
    private string $endDate;

    public function __construct(EntityManagerInterface $entityManager, SemesterService $semesterService)
    {
        $this->entityManager = $entityManager;
        $this->semesterService = $semesterService;
        $this->startDate = $this->semesterService->getPreviousYear()['winter_semester']['start_date'];
        $this->endDate = $this->semesterService->getCurrentYear()['summer_semester']['end_date'];
    }

    public function isDataEmpty(string $entityClass): bool
    {
        return $this->entityManager->getRepository($entityClass)->count() === 0;
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

        $subjects = $this->entityManager->getRepository(Subject::class)->findAll();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Faculty')->execute();
        $savedFaculties = ['IiJM', 'AOJ', 'SWFiS'];
        // Manual addition of faculties
        foreach ($savedFaculties as $facultyName) {
            $faculty = new Faculty();
            $faculty->setName($facultyName);
            $this->entityManager->persist($faculty);
        }
        $this->entityManager->flush();

        foreach ($subjects as $subject) {
            $results = $this->splitSubjectString($subject->getName());
            $faculty = null;
            $major = null;

            $facultyName = $this->unifyFacultyName($results[0]);
            if (!empty($facultyName)) {
                $existingFaculty = $this->entityManager->getRepository(Faculty::class)->findOneBy(['name' => $facultyName]);
                if (!$existingFaculty && !in_array($facultyName, $savedFaculties)) {
                    $faculty = new Faculty();
                    $faculty->setName($facultyName);
                    $this->entityManager->persist($faculty);
                    $savedFaculties[] = $facultyName;
                    $this->entityManager->flush();
                } else {
                    $faculty = $existingFaculty;
                }
            }

            $majorName = $results[1];
            if (!empty($majorName)) {
                $major = $this->entityManager->getRepository(Major::class)->findOneBy(['name' => $majorName]);
                if (!$major) {
                    $major = new Major();
                    $major->setName($majorName);
                    $major->setFaculty($faculty);
                    $this->entityManager->persist($major);
                    $this->entityManager->flush();
                }
            }


            $isStationary = match ($results[2]) {
                'SS' => true,
                'SN' => false,
                default => null,
            };
            $degree = Degree::from($results[3]);

            $subject->setFaculty($faculty);
            $subject->setStationary($isStationary);
            $subject->setDegree($degree);
            $subject->setMajor($major);
        }
        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->createRelationFacultyRoom();
    }


    public function scrapeStudents(array $indexes = []): void
    {
        $lessons = $this->entityManager->getRepository(Lesson::class)->findAll();
        $viableIndexes = $indexes ?: range(0, 60000);
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
                    if ($lesson->getTeacher()->getName() === $item['worker']
                        && $lesson->getStart()->format('Y-m-d\TH:i:s') === $item['start']
                        && $lesson->getFinish()->format('Y-m-d\TH:i:s') === $item['end']
                        && $lesson->getRoom() !== null
                        && $lesson->getRoom()->getName() === $item['room']) {
                        $student->addGroup($lesson->getStudentGroup());
                        $student->addLesson($lesson);
                    }
                }
            }
            $this->entityManager->persist($student);
            $this->entityManager->flush();
            echo 'Student ' . $index . ' scraped' . PHP_EOL;
        }
        $this->entityManager->flush();
        $this->entityManager->clear();
    }


    public function scrapeLessons(DateTimeInterface $start = null, DateTimeInterface $finish = null): void
    {
        if ($start === null) {
            $start = new \DateTime($this->startDate);
        }
        if ($finish === null) {
            $finish = new \DateTime($this->endDate);
        }

        $start = $start->format('Y-m-d');
        $finish = $finish->format('Y-m-d');

        if ($this->isDataEmpty(Teacher::class)) {
            return;
        }

        $this->entityManager->createQuery('DELETE FROM App\Entity\Lesson l WHERE l.start >= :start AND l.finish <= :finish')
            ->setParameter('start', $start)
            ->setParameter('finish', $finish)
            ->execute();

        $teachers = $this->entityManager->getRepository(Teacher::class)->findAll();
        $subjects = $this->entityManager->getRepository(Subject::class)->findAll();

//        $suggestedNames = ['Pluciński Marcin', 'Banaś Joanna', 'Mościcki Mirosław', 'Pejaś Jerzy', 'El Fray Imed',
//            'Karczmarczyk Artur', 'Klęsk Przemysław', "Śmietanka Łukasz", "Śliwiński Grzegorz", "Łosiewicz Zbigniew",
//            "Łazoryszczak Mirosław", "Wysocki Włodzimierz", "Twardochleb Michał", "Trubiłko Joanna", "Sulikowski Piotr",
//            "Sklyar Grigorij", "Siedlecki Krzysztof", "Rozenberg Leonard", "Radliński Łukasz", "Poliwoda Maciej", "Piela Piotr",
//            "Olejnik-Krugły Agnieszka", "Nowosielski Adam", "Mąka Tomasz", "Mościcki Mirosław", "Lewandowska Anna", "Kołodziejczyk Joanna",
//            "Korytkowski Przemysław", "Karczmarczyk Aleksandra", "Kapruziak Mariusz", "Hyla Tomasz", "Fedorov Mykhailo",
//            "Fabisiak Luiza", "Dziśko Maja", "Błaszczyński Tomasz", "Bortko Kamil", "Barcz Anna", "Wernikowski Marek", "Wernikowski Sławomir"];
//        $suggestedTeachers = $this->entityManager->getRepository(Teacher::class)->findBy(['name' => $suggestedNames]);
//        $teachers = $suggestedTeachers;

        foreach ($teachers as $teacher) {
            $name = $teacher->getName();
            $name = str_replace(' ', '%20', $name);
            $url = 'https://plan.zut.edu.pl/schedule_student.php?teacher=' . $name . '&start=' . $start . '&end=' . $finish;
            $response = file_get_contents($url);
            $fullData = json_decode($response, true);
            foreach ($fullData as $data) {
                if (empty($data['title'])) {
                    continue;
                }
                $lesson = new Lesson();
                $lesson->setName($data['title']);
                if ($data['lesson_status'] === 'konsultacje') {
                    $lesson->setFormLesson('konsultacje');
                } else {
                    $lesson->setFormLesson($data['lesson_form']);
                }
                $lesson->setHours($data['hours']);
                $lesson->setStart(new \DateTime($data['start']));
                $lesson->setFinish(new \DateTime($data['end']));
                $lesson->setTeacher($teacher);
                $room = $this->entityManager->getRepository(Room::class)->findOneBy(['name' => $data['room']]);
                $lesson->setRoom($room);
                $isStationary = $data['tok_name'] ? $this->determineStationary($data['tok_name']) : null;
                foreach ($subjects as $subject) {
                    if (stripos($subject->getName(), $data['subject']) !== false
                        && $subject->isStationary() === $isStationary
                        && ($subject->getFaculty() && $room->getFaculty()
                            && ($subject->getFaculty()->getName() === $room->getFaculty()->getName()
                                || $room->getFaculty()->getName() === 'IiJM'))) {
                        $lesson->setSubject($subject);
                        break;
                    }
                }
                // The commented fragment needs to be fixed bc it adds the same subject multiple times
//                if (!$lesson->getSubject()) {
//                    $subject = new Subject();
//                    $subject->setStationary($isStationary);
//                    //echo 'Subject ' . $data['subject'] . ' not found' . PHP_EOL;
//                    if($data['lesson_status'] === 'konsultacje' || !$room){
//
//                        $faculty = " ";
//                    }
//                    else {
//                        $faculty = $room->getFaculty()->getName();
//                        $subject->setFaculty($room->getFaculty());
//                    }
//                    $subjectName = $data['subject'];
//                    $isStationaryString = match ($isStationary) {
//                        true => 'SS',
//                        false => 'SN',
//                        default => " ",
//                    };
//                    $subjectName = $subjectName . ' (' . $faculty . ', ,' . $isStationaryString . ', )';
//                    $subject->setName($subjectName);
//
//                    $this->entityManager->persist($subject);
//                    $this->entityManager->flush();
//                    $lesson->setSubject($subject);
//                    echo 'Subject ' . $subjectName . ' added' . PHP_EOL;
//
//                }
                // Check if the group already exists
                if (!empty($data['group_name'])) {
                    $group = $this->entityManager->getRepository(Group::class)->findOneBy(['name' => $data['group_name']]);
                    if (!$group) {
                        $group = new Group();
                        $group->setName($data['group_name']);
                        $this->entityManager->persist($group);
                        $this->entityManager->flush();
                    }
                    $lesson->setStudentGroup($group);
                }
                $this->entityManager->persist($lesson);

            }

        }
        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    private function splitSubjectString(string $subject): array
    {
        $resultArray = substr($subject, strrpos($subject, '(') + 1, -1);
        $resultArray = explode(',', $resultArray);
        $resultArray = array_map('trim', $resultArray);
        return $resultArray;
    }

    private function unifyFacultyName(string $name): string
    {
        if ($name === 'WBiIS') {
            $name = 'WBiIŚ';
        }
        if ($name === 'WTiICh') {
            $name = 'WTiICH';
        }
        return $name;
    }

    private function determineStationary(string $tokName): bool
    {
        $parts = explode('_', $tokName);
        return isset($parts[2]) && strtoupper($parts[2]) === 'S';
    }

    private function createRelationFacultyRoom(): void
    {
        $faculties = $this->entityManager->getRepository(Faculty::class)->findAll();
        $rooms = $this->entityManager->getRepository(Room::class)->findAll();

        foreach ($rooms as $room) {
            $room->setFaculty(null);
            foreach ($faculties as $faculty) {
                if (preg_match('/(?<![a-zA-Z])' . preg_quote($faculty->getName(), '/') . '(?![a-zA-Z])/', $room->getName())) {
                    $room->setFaculty($faculty);
                    break;
                }
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    public function deleteAll(): void
    {
        $this->entityManager->createQuery('DELETE FROM App\Entity\Lesson')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Group')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Student')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Room')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Teacher')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Subject')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Faculty')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Major')->execute();
        $this->entityManager->getConnection()->executeStatement('DELETE FROM group_student');
        $this->entityManager->getConnection()->executeStatement('DELETE FROM student_lesson');

    }

    // This function periodically updates data in the current week
    public function updateData(): void
    {
        // get the newest createdAt date from the lessons
        $lastUpdateDate = $this->entityManager->getRepository(Lesson::class)->findOneBy([], ['createdAt' => 'DESC']);
        // if less than 12 hours have passed since the last update, return
        if ($lastUpdateDate && $lastUpdateDate->getCreatedAt() > new \DateTime('now - 12 hours')) {
            echo 'Data already updated' . PHP_EOL;
            return;
        }
        $start = new \DateTime('now');
        $finish = new \DateTime('now + 7 days');
        $start->setTime(0, 0, 0);
        $finish->setTime(23, 59, 59);

        $this->scrapeLessons($start, $finish);

        $students = $this->entityManager->getRepository(Student::class)->findAll();
        $indexes = [];
        foreach ($students as $student) {
            $indexes[] = $student->getIndexNumber();
        }

        $this->scrapeStudents($indexes);

        echo 'Data updated' . PHP_EOL;
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
