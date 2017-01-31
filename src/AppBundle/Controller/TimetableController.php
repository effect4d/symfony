<?php

namespace AppBundle\Controller;

use AppBundle\Form\TimetableType;
use AppBundle\Form\SubscriptionType;
use AppBundle\Entity\Timetable;
use AppBundle\Entity\Subscription;
use AppBundle\Entity\User;
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
        $entityManager = $this->getDoctrine()->getRepository(Timetable::class);        
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
        
    /**
     * @Route("/admin/timetable/notice/{id}", requirements={"id": "\d+"}, name="timetable_notice")
     * @Method({"GET", "POST"})
     */
    public function noticeAction(Request $request)
    {
        $routeParams = $request->get('_route_params'); 
        $msgEmail = $request->request->get('email');
        $msgSms = $request->request->get('sms');
        
        $entityManager = $this->getDoctrine()->getRepository(User::class);        
        $query = $entityManager->createQueryBuilder('user')
            ->select('user.id, user.username, user.email, user.phone, subscriptions.type')
            ->innerJoin('user.subscriptions', 'subscriptions')
            ->where('subscriptions.timetable = :timetable')
            ->setParameter('timetable', $routeParams['id'])
            ->getQuery();
        $users = $query->getResult();
        
        foreach ($users as $user) {
            if ($user['type'] == 'EMAIL' && $msgEmail) {
                $this->get('old_sound_rabbit_mq.email_producer')->publish(json_encode([
                    'id' => $user['id'],
                    'text' => $msgEmail,
                ]));
            }
            
            if ($user['type'] == 'PHONE' && $msgSms) {
                $this->get('old_sound_rabbit_mq.sms_producer')->publish(json_encode([
                    'id' => $user['id'],
                    'text' => $msgSms,
                ]));
            }
        }
        
        if ($msgSms) {
            $this->get('old_sound_rabbit_mq.sms_producer')->publish(json_encode([
                'id' => $routeParams['id'],
                'text' => $msgSms,
            ]));
        }
        
        if ($msgEmail || $msgSms) {
            return $this->redirectToRoute('timetable');
        }
                
        return $this->render('timetable/notice.html.twig', [
            'id' => $routeParams['id'],
            'users' => $users,
        ]);
    }
}
