<?php

namespace AppBundle\Controller;

use AppBundle\Form\TimetableType;
use AppBundle\Entity\Timetable;
use AppBundle\Entity\Subscription;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class TimetableController extends Controller
{
    /**
     * @Route("/timetable", name="timetable")
     */
    public function indexAction(Request $request)
    {        
        $entityManager = $this->getDoctrine()->getRepository('AppBundle:Timetable');
        $query = $entityManager->createQueryBuilder('timetable')
            ->select('timetable.id, timetable.name, timetable.trainer, timetable.description, subscriptions.id as sid, subscriptions.type')
            ->leftJoin("timetable.subscriptions", "subscriptions")
            ->where('subscriptions.user = :user or subscriptions.user is null')
            ->setParameter('user', $this->getUser()->getId())
            ->getQuery();
        $timetables = $query->getResult();
        
        return $this->render('timetable/index.html.twig', [
            'timetables' => $timetables,
        ]);
    }
    
    /**
     * @Route("/timetable/subscribe/{id}", requirements={"id": "\d+"}, name="timetable_subscribe")
     * @Method({"GET", "POST"})
     */
    public function subscribeAction(Timetable $timetable, Request $request)
    {       
        $subscription = new Subscription();
        $entityManager = $this->getDoctrine()->getManager();
        $form = $this->createForm(TimetableType::class, $subscription);
        
        $subscription->setTimetable($timetable);
        $subscription->setUser($this->getUser());
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {            
            $em = $this->getDoctrine()->getManager();
            $em->persist($subscription);
            $em->flush();
        
            return $this->redirectToRoute('timetable');
        }

        return $this->render('timetable/subscribe.html.twig', [
            'form' => $form->createView(),
            'subscription' => $subscription,
        ]);
    }
    
    /**
     * @Route("/timetable/unsubscribe/{id}", requirements={"id": "\d+"}, name="timetable_unsubscribe")
     * @Method({"GET", "POST"})
     */
    public function unsubscribeAction(Subscription $subscription, Request $request)
    {       
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($subscription);
        $entityManager->flush();
        
        return $this->redirectToRoute('timetable');
    }
}
