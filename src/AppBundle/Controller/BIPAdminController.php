<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Entity\File;
use AppBundle\Entity\Member;
use AppBundle\Entity\Organ;
use AppBundle\Entity\Submenu;
use AppBundle\Entity\Log;
use AppBundle\Exception\BIPNotFoundException;
use Symfony\Component\Form\Extension\Core\Type\FormType;
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
     * @Route("/admin/managament/")
     */
    public function managamentAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $BIPManager = $this->get('bip_manager');
        try {
            $bip = $BIPManager->getCurrentBIP();
            $BIPManager->checkAdmin($bip, $this->getUser());
        }catch(BIPNotFoundException $e){
            return $e->redirectResponse;
        }

        $organ = new Organ();
        $organ->setBip($bip);
        $form_organ = $this->get('form.factory')->createNamedBuilder('organ', FormType::class, $organ)
                        ->add('organ')
                        ->getForm();
        if($request->request->has('organ')){
            $form_organ->handleRequest($request);
        }
        if($form_organ->isValid()){
            $em->persist($form_organ);
            $em->flush();
        }

        $member = new Member();
        $form_member = $this->get('form.factory')->createNamedBuilder('member', FormType::class, $member)
            ->add('firstname')
            ->add('lastname')
            ->add('organ', EntityType::class, array(
                'class' => 'AppBundle:Organ',
                'query_builder' => function (EntityRepository $er) use($bip) {
                    return $er->createQueryBuilder('o')
                        ->where('o.bip='.$bip->getId())
                        ->orderBy('o.organ', 'ASC');
                },
            ))
            ->getForm();

        if($request->request->has('member')){
            $form_member->handleRequest($request);
        }
        if($form_member->isValid()){
            $em->persist($form_member);
            $em->flush();
        }

        $organy = $em->getRepository("AppBundle:Organ")->findByBip($bip);
        foreach($organy as $k=>$v){
            $organy[$k]->setMembers($em->getRepository('AppBundle:Member')->findByOrgan($v));
        }

        return $this->render('user/zarzad.html.twig', array(
            'bip'=>$bip,
            'form_organ'=>$form_organ,
            'organy'=>$organy,
        ));
    }

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

//    /**
//<<<<<<< HEAD
//     * @Route("/admin/add/art/", name="admin_add_art")
//     */
//    public function adminAddArtAction(Request $request)
//    {
//        $em = $this->getDoctrine()->getManager();
//        $BIPManager = $this->get('bip_manager');
//        try {
//            $bip = $BIPManager->getCurrentBIP();
//            $BIPManager->checkAdmin($bip, $this->getUser());
//        }catch(BIPNotFoundException $e){
//            return $e->redirectResponse;
//        }
//
//        $article = new Article();
//        $article->setCreated(new \DateTime(date('Y-m-d H:i:s')));
//        $article->setAuthor($this->getUser());
//        $form = $this->createFormBuilder($article)
//            ->add('title')
//            ->add('menu', EntityType::class, array(
//                'class' => 'AppBundle:Submenu',
//                'choice_label' => 'name',
//                'query_builder' => function (EntityRepository $er) use ($bip){
//                    return $er->createQueryBuilder('s')
//                        ->where("s.bip= ".$bip->getId());
//                },
//            ))
//            ->add('content', TextareaType::class)
//            ->add('save', SubmitType::class)
//            ->getForm();
//
//        $form->handleRequest($request);
//        if($form->isValid()){
//            $em->persist($article);
//            $this->addFlash('notice', 'Pomyślnie dodano artykuł do menu.');
//            $em->flush();
//
//        }
//
//        return $this->render('user/add_article.html.twig', array(
//            'form'=>$form->createView(),
//            'bip' => $bip,
//        ));
//    }
//
//    /**
//     * @Route("/admin/view/art/", name="admin_view_art")
//     */
//    public function adminViewArtAction()
//    {
//        $em = $this->getDoctrine()->getManager();
//        $BIPManager = $this->get('bip_manager');
//        try {
//            $bip = $BIPManager->getCurrentBIP();
//            $BIPManager->checkAdmin($bip, $this->getUser());
//        }catch(BIPNotFoundException $e){
//            return $e->redirectResponse;
//        }
//        $articles = $em->getRepository("AppBundle:Article");
//        $qb = $articles->createQueryBuilder('a');
//        $articles1 = $qb
//                    ->innerJoin('a.menu', 'm')
//                    ->innerJoin('m.bip', 'b')
////                    ->innerJoin('a.section', 's')
////                    ->innerJoin('s.menu','z')
////                    ->innerJoin('z.bip', 'y')
//                    ->where('b.id='.$bip->getId())->getQuery()->getResult();
//        $qb2 = $articles->createQueryBuilder('s');
//        $sections = $qb2
//                    ->innerJoin('s.section', 'x')
//                    ->innerJoin('x.menu', 'y')
//                    ->innerJoin('y.bip', 'p')
//                    ->where('p.id='.$bip->getId())->getQuery()->getResult();
//
//        return $this->render('user/view_articles.html.twig', array(
//            'bip' => $bip,
//            'articles' => $articles1,
//            'sections' => $sections,
//        ));
//    }
//
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
     * @Route("/admin/edit/{art}/menu/", name="admin_edit_art_menu")
     */
    public function adminEditArtMenuAction(Request $request, $art)
    {
        $em = $this->getDoctrine()->getManager();
        $BIPManager = $this->get('bip_manager');
        try {
            $bip = $BIPManager->getCurrentBIP();
        }catch(BIPNotFoundException $e){
            return $e->redirectResponse;
        }
        $article = $em->getRepository("AppBundle:Article")->find($art);



        $form = $this->get('form.factory')->createNamedBuilder('article', FormType::class, $article)
            ->add('title')
            ->add('menu', EntityType::class, array(
                'class' => 'AppBundle:Submenu',
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) use ($bip){
                    return $er->createQueryBuilder('s')
                        ->where("s.bip= ".$bip->getId());
                },
            ))
            ->add('content', TextareaType::class)
            ->add('save', SubmitType::class)
            ->getForm();
        if($request->request->has('article')) {
            $form->handleRequest($request);
        }
        if($form->isValid()){
            $log = new Log();
            $log->setArticle($article);
            $log->setEditor($this->getUser());
            $log->setEdited(new \DateTime(date('Y-m-d H:i:s')));
            $em->persist($log);
            $this->addFlash('notice', "Pomyślnie zaktualizowano artykuł.");
            $em->flush();
        }

        $file = new File();
        $form_file = $this->get('form.factory')->createNamedBuilder('file', FormType::class, $file)
            ->add('name')
            ->add('file')
            ->getForm();
        if($request->request->has('file')) {
            $form_file->handleRequest($request);
        }

        if ($form_file->isValid()) {
            $file->setArticle($article);
            $file->upload();
            $em->persist($file);
            $em->flush();
        }

        $attachments = $em->getRepository('AppBundle:File')->findByArticle($article);

        return $this->render('user/edit_art_menu.html.twig', array(
            'bip'=>$bip,
            'form'=>$form->createView(),
            'form_file'=>$form_file->createView(),
            'attachments'=>$attachments,
        ));
    }

    /**
     * @Route("/admin/edit/{art}/section/", name="admin_edit_art_section")
     */
    public function adminEditArtSectionAction(Request $request, $art)
    {
        $em = $this->getDoctrine()->getManager();
        $BIPManager = $this->get('bip_manager');
        try {
            $bip = $BIPManager->getCurrentBIP();
        }catch(BIPNotFoundException $e){
            return $e->redirectResponse;
        }
        $article = $em->getRepository("AppBundle:Article")->find($art);

        $log = new Log();
        $log->setArticle($article);
        $log->setEditor($this->getUser());
        $log->setEdited(new \DateTime(date('Y-m-d H:i:s')));

        $form = $this->get('form.factory')->createNamedBuilder('article', FormType::class, $article)
            ->add('title')
            ->add('section', EntityType::class, array(
                'class' => 'AppBundle:Article',
                'choice_label' => 'title',
                'query_builder' => function (EntityRepository $er) use ($bip){
                    return $er->createQueryBuilder('a')
                        ->innerJoin('a.menu', 'm')
                        ->where("m.bip= ".$bip->getId());
                },
            ))
            ->add('content', TextareaType::class)
            ->add('save', SubmitType::class)
            ->getForm();
        if($request->request->has('article')) {
            $form->handleRequest($request);
        }
        if($form->isValid()){
            $em->persist($log);
            $this->addFlash('notice', "Pomyślnie zaktualizowano artykuł.");
            $em->flush();
        }


        $file = new File();
        $form_file = $this->get('form.factory')->createNamedBuilder('file', FormType::class, $file)
            ->add('name')
            ->add('file')
            ->getForm();
        if($request->request->has('file')) {
            $form_file->handleRequest($request);
        }

        if ($form_file->isValid()) {
            $file->setArticle($article);
            $file->upload();
            $em->persist($file);
            $em->flush();
        }

        $attachments = $em->getRepository('AppBundle:File')->findByArticle($article);

        return $this->render('user/edit_art_section.html.twig', array(
            'bip'=>$bip,
            'form'=>$form->createView(),
            'form_file'=>$form_file->createView(),
            'attachments'=>$attachments,
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
        $musthave = $em->getRepository('AppBundle:StaticArt')->findByBip($bip);
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
            'musthave'=>$musthave,
            'form'=>$form->createView(),
        ));
    }

    /**
     * @Route("admin/dane/public/", name="admin_manage_public")
     */
    public function adminManagePublicAction()
    {
        $em = $this->getDoctrine()->getManager();
        $BIPManager = $this->get('bip_manager');
        $bip = $BIPManager->getCurrentBIP();
        $bip = $em->getRepository("AppBundle:Bip")->find($bip);
        $currentStatus= $bip->getPublic();

        if($currentStatus==false){
            $bip->setPublic(true);
            $em->flush();
            $this->addFlash('notice', 'witajci');
        }
        else {
            $bip->setPublic(false);
            $em->flush();
            $this->addFlash('notice', 'żegnajci');
        }

        return $this->redirectToRoute('admin_edit_dane');
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
            ->add('nazwisko')
            ->add('imie')
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
            ->add('nazwisko')
            ->add('imie')
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
