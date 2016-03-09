<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Bip;
use AppBundle\Form\Type\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\User;

class BIPController extends Controller
{

    /**
     * @Route("/bezlog/", name="bezlog")
     */
    public function bezlogAction()
    {
        return $this->render('user/show_content.html.twig');
    }

    /**
     * @Route("/bip/", name="bip")
     */
    public function  bipAction()
    {
        return $this->render('bip/index.html.twig');
    }

    /**
     * @Route("/admin/{bip}/menu/", name="menu")
     */
    public function  BIPMenuAction(Request $request, $bip)
    {
        $em = $this->getDoctrine()->getManager();
        $bip = $em->getRepository('AppBundle:Bip')->find($bip);
        $menu = $em->getRepository('AppBundle:SubMenu')->findBybip($bip);

        return $this->render('user/show_content.html.twig', array(
            'menu'=>$menu,
        ));
    }

    /**
     * @Route("/test/", name="test")
     */
    public function TestAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $bip = new Bip();
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if($form->isValid()){
//            $bip->setUrl('asdf');
//            $bip->setLogo('hearh');
            $em->persist($bip);
            $em->flush();
            print "Pomyślnie dodano !";
        }

        return $this->render('form.html.twig', array(
            'form'=>$form->createView(),
        ));
    }
}
