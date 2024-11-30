<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ZutScheduleController extends AbstractController
{
    #[Route('/', name: 'zutScheduleHome')]
    public function index(): Response
    {
        return $this->render('ZutScheduleIndex.html.twig', [
            'title' => 'ZUT Schedule',
        ]);
    }
}
