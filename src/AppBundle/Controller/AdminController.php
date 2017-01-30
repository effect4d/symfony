<?php

namespace AppBundle\Controller;

use AppBundle\Form\UserType;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;

class AdminController extends Controller
{
    /**
     * @Route("/admin", name="admin")
     */
    public function indexAction(Request $request)
    {        
        $entityManager = $this->getDoctrine()->getManager();
        $users = $entityManager->getRepository(User::class)->findAll();
        
        return $this->render('admin/index.html.twig', [
            'users' => $users,
        ]);
    }
    
    /**
     * @Route("/admin/register", name="create")
     */
    public function createAction(Request $request)
    {
        $user = new User();
        
        $form = $this->createForm(UserType::class, $user, [
            'type' => 'create',
        ]);
        $form->handleRequest($request);
                
        if ($form->isSubmitted() && $form->isValid()) {
            $password = $this->get('security.password_encoder')->encodePassword($user, $user->getPassword());
            $user->setPassword($password);
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/user/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    /**
     * @Route("/admin/user/edit/{id}", requirements={"id": "\d+"}, name="user_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(User $user, Request $request)
    {       
        $entityManager = $this->getDoctrine()->getManager();
        $form = $this->createForm(UserType::class, $user, [
            'type' => 'edit',
        ]);
        
        $oldPassword = $user->getPassword();
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $password = $this->get('security.password_encoder')->encodePassword($user, $user->getPassword());
            $user->setPassword($password);
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
        
            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
