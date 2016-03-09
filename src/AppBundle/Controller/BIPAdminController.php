<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Entity\Submenu;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
            ->add('content')
            ->add('save', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);
        if($form->isValid()){
            $em->persist($article);
            $em->flush();
            $this->addFlash('success', 'Pomyślnie dodano artykuł do menu.');
        }

        return $this->render('user/add_article.html.twig', array(
            'form'=>$form->createView(),
            'bip' => $bip,
        ));
    }
}
