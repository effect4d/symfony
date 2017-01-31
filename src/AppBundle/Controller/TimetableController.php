<?php

namespace AppBundle\Controller;

use AppBundle\Form\TimetableType;
use AppBundle\Form\SubscriptionType;
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
            ->leftJoin('timetable.subscriptions', 'subscriptions', 'WITH', 'subscriptions.user = :user')
            ->setParameter('user', $this->getUser()->getId())
            ->groupBy('timetable.id')
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
        $form = $this->createForm(SubscriptionType::class, $subscription);
        
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
    
    /**
     * @Route("/admin/timetable/create", name="timetable_create")
     */
    public function createAction(Request $request)
    {
        $timetable = new Timetable();
        
        $form = $this->createForm(TimetableType::class, $timetable);
        $form->handleRequest($request);
                
        if ($form->isSubmitted() && $form->isValid()) {            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($timetable);
            $entityManager->flush();

            return $this->redirectToRoute('timetable');
        }

        return $this->render('timetable/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    /**
     * @Route("/admin/timetable/edit/{id}", requirements={"id": "\d+"}, name="timetable_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Timetable $timetable, Request $request)
    {       
        $form = $this->createForm(TimetableType::class, $timetable);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($timetable);
            $entityManager->flush();
        
            return $this->redirectToRoute('timetable');
        }

        return $this->render('timetable/edit.html.twig', [
            'timetable' => $timetable,
            'form' => $form->createView(),
        ]);
    }
}
