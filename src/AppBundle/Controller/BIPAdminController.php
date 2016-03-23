<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Entity\Submenu;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use UserBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class BIPAdminController extends Controller implements AuthenticatedController
{
    /**
     * @Route("/admin/add/menu/", name="admin_add_menu")
     */
    public function  BIPMenuAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $BIPManager = $this->get('bip_manager');
        try {
            $bip = $BIPManager->getCurrentBIP();
            $BIPManager->checkAdmin($bip, $this->getUser());
        }catch(BIPNotFoundException $e){
            return $e->redirectResponse;
        }
        $menu = $em->getRepository('AppBundle:Submenu')->findBybip($bip);

        $submenu = new Submenu();
        $submenu->setBip($bip);
        $submenu->setPosition(count($menu)+1);
        $form = $this->createFormBuilder($submenu)
            ->add('name')
            ->add('save', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if($form->isValid()){
            $em->persist($submenu);
            $em->flush();
            $this->addFlash('success', 'Pomyślnie dodano pozycję do menu.');
            $menu = $em->getRepository('AppBundle:Submenu')->findBybip($bip);
        }


        return $this->render('user/menu.html.twig', array(
            'bip'=>$bip,
            'menu'=>$menu,
            'form'=>$form->createView(),
        ));
    }

    /**
     * @Route("/admin/menu/{menu}/", name="admin_edit_menu")
     */
    public function adminEditMenuAction(Request $request, $menu)
    {
        $em = $this->getDoctrine()->getManager();
        $BIPManager = $this->get('bip_manager');
        try {
            $bip = $BIPManager->getCurrentBIP();
            $BIPManager->checkAdmin($bip, $this->getUser());
        }catch(BIPNotFoundException $e){
            return $e->redirectResponse;
        }
        $menu = $em->getRepository('AppBundle:Submenu')->find($menu);

        $form = $this->createFormBuilder($menu)
            ->add('name')
            ->add('save', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if($form->isValid()){
            $em->persist($menu);
            $em->flush();
            $this->addFlash('success', 'Pomyślnie dodano pozycję do menu.');
        }
        return $this->render('user/edit_menu.html.twig', array(
            'bip'=>$bip,
            'form'=>$form->createView(),
        ));
    }

    /**
     * @Route("/admin/remove/{menu}/", name="admin_remove_menu")
     */
    public function adminRemoveMenuAction(Request $request, $menu)
    {
        $em = $this->getDoctrine()->getManager();
        $BIPManager = $this->get('bip_manager');
        try {
            $bip = $BIPManager->getCurrentBIP();
            $BIPManager->checkAdmin($bip, $this->getUser());
        }catch(BIPNotFoundException $e){
            return $e->redirectResponse;
        }
        $menu = $em->getRepository('AppBundle:Submenu')->find($menu);
        $em->remove($menu);
        $em->flush();

        return $this->redirectToRoute('admin_add_menu', array(
            'bip'=>$bip->getId(),
        ));
    }

    /**
     * @Route("/admin/", name="admin_view_profile")
     */
    public function adminViewAction()
    {
        $em = $this->getDoctrine()->getManager();
        $BIPManager = $this->get('bip_manager');
        try {
            $bip = $BIPManager->getCurrentBIP();
            $BIPManager->checkAdmin($bip, $this->getUser());
        }catch(BIPNotFoundException $e){
            return $e->redirectResponse;
        }

        return $this->render('user/show_content.html.twig', array(
            'bip'=>$bip,
        ));
    }

    /**
     * @Route("/admin/dane/edit/", name="admin_edit_dane")
     */
    public function adminEditDaneAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $BIPManager = $this->get('bip_manager');
        try {
            $bip = $BIPManager->getCurrentBIP();
            $BIPManager->checkAdmin($bip, $this->getUser());
        }catch(BIPNotFoundException $e){
            return $e->redirectResponse;
        }
        $bip_dane = $em->getRepository("AppBundle:Bip")->find($bip);
        $form = $this->createFormBuilder($bip_dane)
            ->add('name')
            ->add('file')
            ->add('save', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if($form->isValid()){
            $bip_dane->upload();
            $this->addFlash('notice', "Pomyślnie zaktualizowano dane.");
            $em->flush();
        }

        return $this->render('user/edit_dane.html.twig', array(
            'bip'=>$bip,
            'form'=>$form->createView(),
        ));
    }

    /**
     * @Route("/admin/", name="admin_index")
     */
    public function adminIndexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $BIPManager = $this->get('bip_manager');
        $bip = $BIPManager->getCurrentBIP();
        $bip = $em->getRepository("AppBundle:Bip")->find($bip);

        return $this->render("user/index_admin.html.twig", array(
           'bip'=>$bip,
        ));
    }

    /**
     * @Route("/admin/users/", name="admin_users_list")
     */
    public function adminUsersListAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $BIPManager = $this->get('bip_manager');
        $bip = $BIPManager->getCurrentBIP();
//        $bip_dane = $em->getRepository("AppBundle:Bip")->find($bip);
        $users = $em->getRepository("UserBundle:User");
        $qb = $users->createQueryBuilder('u');
        $qb->where('u.bip=:bip')->setParameter('bip', $bip);
        $query = $qb->getQuery();

        $paginator = $this->get('knp_paginator');
        $users = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('user/view_users.html.twig', array(
            'bip'=>$bip,
            'users'=>$users,
        ));
    }

    /**
     * @Route("/admin/user/edit/{user}/", name="admin_user_edit")
     */
    public function adminUserEditAction(Request $request, $user)
    {
        $em = $this->getDoctrine()->getManager();
        $BIPManager = $this->get('bip_manager');
        $bip = $BIPManager->getCurrentBIP();
        $user = $em->getRepository("UserBundle:User")->find($user);

        $form = $this->createFormBuilder($user)
            ->add('username')
            ->add('email')
            ->add('save', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if($form->isValid()){
            $this->addFlash('notice', "Pomyślnie zaktualizowano użytkownika.");
            $em->flush();
            return $this->redirectToRoute('admin_users_list');
        }

        return $this->render('user/edit_user.html.twig', array(
           'bip'=>$bip,
            'form'=>$form->createView(),
        ));
    }

    /**
     * @Route("/admin/user/add/", name="admin_user_add")
     */
    public function adminUserAddAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $BIPManager = $this->get('bip_manager');
        $bip = $BIPManager->getCurrentBIP();

//        $roles[] = 'ROLE_USER';

        $user = new User();
        $user->setBip($bip);


        $form = $this->createFormBuilder($user)
            ->add('username')
            ->add('email')
            ->add('plainPassword', RepeatedType::class, array(
                'type' => PasswordType::class,
                'first_options'  => array('label' => 'Password'),
                'second_options' => array('label' => 'Repeat Password'),
            ))
            ->add('save', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);
        if($form->isValid()){
            $user->addRole('ROLE_USER');
            $user->setEnabled(TRUE);
            $em->persist($user);
            $em->flush();
            $this->addFlash('notice', "Pomyślnie dodano użytkownika.");
            return $this->redirectToRoute('admin_users_list');
        }

        return $this->render('user/add_user.html.twig', array(
            'bip'=>$bip,
            'form'=>$form->createView(),
        ));
    }

    /**
     * @Route("/bip_menu/", name="bip_lewe_menu")
     */
    public function menuAdminAction($bip){
        $em = $this->getDoctrine()->getManager();
        $BIPManager = $this->get('bip_manager');
        $articles1 = $em->getRepoitory("AppBundle:Article")->findByBip($bip);
        try {
            $bip = $BIPManager->getCurrentBIP();
        }catch(BIPNotFoundException $e){
            return $e->redirectResponse;
        }
//        $bip = $em->getRepository('AppBundle:Bip')->find($bip);

        $submenus = $em->getRepository('AppBundle:Submenu')->findByBip($bip);

        foreach($submenus as $k=>$v){
            $articles = $em->getRepository('AppBundle:Article')->findByMenu($v);
            $submenus[$k]->setArticles($articles);
            $articles = $submenus[$k]->getArticles();
        }

        return $this->render('bip/leftmenu.html.twig', array(
            'submenu'=>$submenus,
            'articles1'=>$articles1,
            'bip'=>$bip,
        ));
    }

    /**
     * @Route("/admin/user/remove/{user}/", name="admin_user_remove")
     */
    public function adminRemoveUserAction(Request $request, $user)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository("UserBundle:User")->find($user);
        $username = $user->getUsername();
        $em->remove($user);
        $em->flush();
        $this->addFlash('notice', "Pomyślnie usunięto użytkownika: ".$username.".");

        return $this->redirectToRoute('admin_users_list');
    }

    /**
     * @Route("/admin/logo/manage/", name="admin_manage_logo")
     */
    public function adminManageLogoAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $BIPManager = $this->get('bip_manager');
        $bip = $BIPManager->getCurrentBIP();

        $bip_dane = $em->getRepository("AppBundle:Bip")->find($bip);
        $form = $this->createFormBuilder($bip_dane)
            ->add('name')
            ->add('logo')
            ->add('save', SubmitType::class)
            ->getForm();

        return $this->render("/user/manage_logo.html.twig", array(
            'bip'=>$bip,
        ));
    }
}
