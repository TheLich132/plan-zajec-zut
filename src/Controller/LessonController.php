<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\LessonRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class LessonController extends AbstractController
{
    #[Route('/api/lessons', name: 'api_lessons', methods: ['GET'])]
    public function getLessons(Request $request, LessonRepository $lessonRepository): JsonResponse
    {
        // Pobieranie filtrów dla planu 1
        $lecturer1 = $request->query->get('lecturer1');
        $room1 = $request->query->get('room1');
        $subject1 = $request->query->get('subject1');
        $group1 = $request->query->get('group1');
        $albumNumber1 = $request->query->get('albumNumber1');

        // Pobieranie filtrów dla planu 2
        $lecturer2 = $request->query->get('lecturer2');
        $room2 = $request->query->get('room2');
        $subject2 = $request->query->get('subject2');
        $group2 = $request->query->get('group2');
        $albumNumber2 = $request->query->get('albumNumber2');

        // Funkcja do budowania zapytań z numerem planu
        $buildQuery = function ($lecturer, $room, $subject, $group, $albumNumber, $planNumber) use ($lessonRepository) {
            $qb = $lessonRepository->createQueryBuilder('l')
                ->leftJoin('l.teacher', 't')
                ->leftJoin('l.room', 'r')
                ->leftJoin('l.subject', 's')
                ->leftJoin('l.studentGroup', 'g')
                ->leftJoin('l.students', 'st');

            if ($lecturer) {
                $qb->andWhere('t.name LIKE :lecturer')
                    ->setParameter('lecturer', "%$lecturer%");
            }

            if ($room) {
                $qb->andWhere('r.name LIKE :room')
                    ->setParameter('room', "%$room%");
            }

            if ($subject) {
                $qb->andWhere('s.name LIKE :subject')
                    ->setParameter('subject', "%$subject%");
            }

            if ($group) {
                $qb->andWhere('g.name LIKE :group')
                    ->setParameter('group', "%$group%");
            }

            if ($albumNumber) {
                $qb->andWhere('st.indexNumber = :albumNumber')
                    ->setParameter('albumNumber', $albumNumber);
            }

            $lessons = $qb->getQuery()->getResult();

            // Dodaj znacznik planu do każdego wyniku
            return array_map(function ($lesson) use ($planNumber) {
                $subject = $lesson->getSubject();
                $teacher = $lesson->getTeacher();
                $room = $lesson->getRoom();
                $studentGroup = $lesson->getStudentGroup();
                $formLesson = $lesson->getFormLesson();

                if (!$subject || !$teacher || !$room || !$studentGroup) {
                    return null;
                }

                return [
                    'title' => $subject->getName(),
                    'start' => $lesson->getStart()?->format('Y-m-d H:i:s'),
                    'end' => $lesson->getFinish()?->format('Y-m-d H:i:s'),
                    'description' => 'Sala: ' . $room->getName() . ', Grupa: ' . $studentGroup->getName() . ', Prowadzący/a: ' . $teacher->getName() . ' - (' . $formLesson . ')',
                    'type' => $formLesson,
                    'plan' => $planNumber, // Dodaj numer planu
                ];
            }, $lessons);
        };

        // Pobieranie wyników dla obu planów


        // Łączenie wyników z obu planów


        $allFiltersEmpty1 =
            !$lecturer1 && !$room1 && !$subject1 && !$group1 && !$albumNumber1;
        $allFiltersEmpty2 =
            !$lecturer2 && !$room2 && !$subject2 && !$group2 && !$albumNumber2;

        $results1 = $buildQuery($lecturer1, $room1, $subject1, $group1, $albumNumber1, '1');
        $results2 = $buildQuery($lecturer2, $room2, $subject2, $group2, $albumNumber2, '2');

        if ($allFiltersEmpty1 && $allFiltersEmpty2) {
            return $this->json([]);
        }
        elseif(!$allFiltersEmpty1 && !$allFiltersEmpty2) {
            $combinedResults = array_merge(array_filter($results1), array_filter($results2));
        }
        else{
            $combinedResults = $results1;
        }

        return $this->json($combinedResults);
    }
}