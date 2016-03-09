<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Submenu;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

class BIPAdminController extends Controller
{
    /**
     * @Route("/admin/{bip}/menu/", name="menu")
     */
    public function  BIPMenuAction(Request $request, $bip)
    {
        $em = $this->getDoctrine()->getManager();
        $bip = $em->getRepository('AppBundle:Bip')->find($bip);
        $menu = $em->getRepository('AppBundle:Submenu')->findBybip($bip);

        $submenu = new Submenu();
        $submenu->setBip($bip);
        $submenu->setPosition(1);
        $form = $this->createFormBuilder($submenu)
            ->add('name')
            ->add('save', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if($form->isValid()){
            $em->persist($submenu);
            $em->flush();
            $this->addFlash('success', 'Pomyślnie dodano pozycję do menu.');
        }

        return $this->render('user/menu.html.twig', array(
            'bip'=>$bip,
            'menu'=>$menu,
            'form'=>$form->createView(),
        ));
    }
}
