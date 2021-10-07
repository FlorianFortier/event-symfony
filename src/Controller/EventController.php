<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Participation;
use App\Form\EventEvaluationType;
use App\Form\EventType;
use App\Form\ParticipationType;
use App\Repository\EventRepository;
use App\Repository\ParticipationRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

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
     * @IsGranted("ROLE_CONTRIBUTOR")
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
     * @IsGranted("ROLE_CONTRIBUTOR")
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
     * @IsGranted("ROLE_ADMIN")
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
    public function  show(Event $event, ParticipationRepository $participationRepository)
    {
        $evaluations = $participationRepository->findByEvent($event);
        return $this->render('event/show.html.twig', [
            'event' => $event,
            'evaluations'=> $evaluations
    ]);
    }

    /**
     * @Route("/event/{event}/participate", name="event_participate")
     * @IsGranted("ROLE_USER")
     */
    public function participate(Event $event, Request $request, EntityManagerInterface $em)
    {
        $participation = new Participation();
        $participation->setEvent($event)
            ->setUser($this->getUser());
        $eventParticipationForm = $this->createForm(ParticipationType::class, $participation);

        $eventParticipationForm->handleRequest($request);
        if ($eventParticipationForm->isSubmitted() && $eventParticipationForm->isValid()) {

            $participation = $eventParticipationForm->getData();
            $em->persist($participation);
            $em->flush();
        }
        return $this->render('event/participate.html.twig', [
            'event_participation_form' => $eventParticipationForm->createView()
        ]);
    }
    /**
     * @Route("/event/{event}/evaluate", name="event_evaluate")
     * @IsGranted("ROLE_USER")
     */
    public function evaluate(Event $event, EntityManagerInterface $em, ParticipationRepository $participationRepository, Request $request)
    {
        $participation = $participationRepository->findOneBy([
            'user' => $this->getUser(),
            'event' => $event
        ]);
        $eventEvaluateForm = $this->createForm(EventEvaluationType::class, $participation);
        $eventEvaluateForm->handleRequest($request);

        if ($eventEvaluateForm->isSubmitted() && $eventEvaluateForm->isValid()) {
            $em->flush();

            return $this->redirectToRoute('event_show', ['event' => $event->getId()]);
        }

        return $this->render('event/evaluate.html.twig', [
            'eventEvaluateForm' => $eventEvaluateForm->createView()
        ]);
    }
}
