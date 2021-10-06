<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    /**
     * @Route("/event", name="event")
     */
    public function index(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findBy([], ['startedAt' => 'ASC']);


        return $this->render('event/index.html.twig', [
            'events' => $events,
        ]);
    }

    /**
     * @Route("/event/add", name="event_add")
     */
    public function add(Request $request, EntityManagerInterface $em)
    {
        $event = new Event();

        $addEventForm = $this->createForm(EventType::class, $event);

        $addEventForm->handleRequest($request);

        if ($addEventForm->isSubmitted() && $addEventForm->isValid()) {
            $event = $addEventForm->getData();

            $em->persist($event);
            $em->flush();

            return $this->redirectToRoute('event_show', [
                'event' => $event->getId()
            ]);
        }

        return $this->render('event/add.html.twig', [
            'addEventForm' => $addEventForm->createView()
        ]);

    }

    /**
     * @Route("/event/{event}/update", name="event_update")
     */
    public function update(Event $event, Request $request, EntityManagerInterface $em)
    {
        $updateEventForm = $this->createForm(EventType::class, $event);

        $updateEventForm->handleRequest($request);

        if ($updateEventForm->isSubmitted() && $updateEventForm->isValid()) {
            $em->flush();
        }

        return $this->render('event/update.html.twig', [
            'updateEventForm' => $updateEventForm->createView(),
            'eventName' => $event->getName()
        ]);

    }

    /**
     * @Route("/event/{event}/delete", name="event_delete")
     */
    public function delete(Event $event, EntityManagerInterface $em)
    {
        $delete = $event->getName().'a bien été supprimé !';
        $em->remove($event);
        $em->flush();

        $this->addFlash('success', $delete);
        return $this->redirectToRoute('event');
    }


    /**
     * @Route("/event/{event}", name="event_show")
     */
    public function  show(Event $event)
    {
        return $this->render('event/show.html.twig', [
            'event' => $event
    ]);
    }
}
