<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Entity\Submenu;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


class BIPAdminController extends Controller
{
    /**
     * @Route("/admin/{bip}/add/menu/", name="admin_add_menu")
     */
    public function  BIPMenuAction(Request $request, $bip)
    {
        $em = $this->getDoctrine()->getManager();
        $bip = $em->getRepository('AppBundle:Bip')->find($bip);
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
     * @Route("/admin/{bip}/menu/{menu}/", name="admin_edit_menu")
     */
    public function adminEditMenuAction(Request $request, $bip, $menu)
    {
        $em = $this->getDoctrine()->getManager();
        $bip = $em->getRepository("AppBundle:Bip")->find($bip);
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
     * @Route("/admin/{bip}/add/art/", name="admin_add_art")
     */
    public function adminAddArtAction(Request $request, $bip)
    {
        $em = $this->getDoctrine()->getManager();
        $bip = $em->getRepository("AppBundle:Bip")->find($bip);

        $article = new Article();
        $form = $this->createFormBuilder($article)
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

        $form->handleRequest($request);
        if($form->isValid()){
            $em->persist($article);
            $this->addFlash('success', 'Pomyślnie dodano artykuł do menu.');
            $em->flush();

        }

        return $this->render('user/add_article.html.twig', array(
            'form'=>$form->createView(),
            'bip' => $bip,
        ));
    }

    /**
     * @Route("/admin/{bip}/view/art/", name="admin_view_art")
     */
    public function adminViewArtAction($bip)
    {
        $em = $this->getDoctrine()->getManager();
        $bip = $em->getRepository("AppBundle:Bip")->find($bip);
        $articles = $em->getRepository("AppBundle:Article");
        $qb = $articles->createQueryBuilder('a');
        $articles = $qb
                    ->innerJoin('a.menu', 'm')
                    ->innerJoin('m.bip', 'b')
                    ->where('b.id='.$bip->getId())->getQuery()->getResult();

        return $this->render('user/view_articles.html.twig', array(
            'bip' => $bip,
            'articles' => $articles,
        ));
    }

    /**
     * @Route("/admin/{bip}/", name="admin_view")
     */
    public function adminViewAction($bip)
    {
        $em = $this->getDoctrine()->getManager();
        $bip = $em->getRepository("AppBundle:Bip")->find($bip);

        return $this->render('user/show_content.html.twig', array(
            'bip'=>$bip,
        ));
    }

    /**
     * @Route("/admin/{bip}/edit/{art}/", name="admin_edit_art")
     */
    public function adminEditArtAction(Request $request, $bip, $art)
    {
        $em = $this->getDoctrine()->getManager();
        $bip = $em->getRepository("AppBundle:Bip")->find($bip);
        $article = $em->getRepository("AppBundle:Article")->find($art);

        $form = $this->createFormBuilder($article)
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
        $form->handleRequest($request);
        if($form->isValid()){
            $this->addFlash('notice', "Pomyślnie zaktualizowano artykuł.");
            $em->flush();
        }

        return $this->render('user/edit_art.html.twig', array(
            'bip'=>$bip,
            'form'=>$form->createView(),
        ));
    }

    /**
     * @Route("/admin/{bip}/remove/{art}/", name="admin_remove_art")
     */
    public function adminRemoveArtAction(Request $request, $bip, $art)
    {
        $em = $this->getDoctrine()->getManager();
        $bip = $em->getRepository("AppBundle:Bip")->find($bip);
        $article = $em->getRepository("AppBundle:Article")->find($art);
        $em->remove($article);
        $em->flush();

        return $this->redirectToRoute('admin_view_art', array(
            'bip'=>$bip->getId(),
        ));
    }

    /**
     * @Route("/admin/{bip}/art/{art}/", name="admin_view_article")
     */
    public function adminArtViewAction($bip, $art)
    {
        $em = $this->getDoctrine()->getManager();
        $bip = $em->getRepository("AppBundle:Bip")->find($bip);
        $article = $em->getRepository("AppBundle:Article")->find($art);


        return $this->render('user/view_art.html.twig', array(
            'bip'=>$bip,
            'article'=>$article,
        ));
    }
}
