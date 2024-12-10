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
        $lecturer = $request->query->get('lecturer');
        $room = $request->query->get('room');
        $subject = $request->query->get('subject');
        $group = $request->query->get('group');
        $albumNumber = $request->query->get('albumNumber');

        if (!$lecturer && !$room && !$subject && !$group && !$albumNumber) {
            return $this->json([]);
        }


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

        // Filtrujemy wyniki, aby pominąć lekcje z brakującymi danymi
        $data = array_values(array_filter(array_map(function ($lesson) {
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
            ];
        }, $lessons)));

        return $this->json($data);
    }
}