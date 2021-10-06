<?php

namespace App\Controller;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findBy([], ['startedAt' => 'ASC'], '3' );

        return $this->render('main/index.html.twig', [
            'events' => $events,
        ]);
    }
}
