<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Bip;
use AppBundle\Entity\Submenu;
=======
>>>>>>> a64d267ef45d513c4ff20271d3f875e0d498a5ca
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

        $submenu = new Submenu();
        $submenu->setBip($bip);
        $form = $this->createFormBuilder($submenu)
                    ->add('name')->getForm();

        return $this->render('user/menu.html.twig', array(
            'menu'=>$menu,
            'form'=>$form->createView(),
        ));
    }
}
