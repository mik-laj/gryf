<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Bip;
use AppBundle\Entity\Submenu;

use AppBundle\Form\Type\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
     * @Route("/bip/{bip}/", name="bip")
     */
    public function  bipAction($bip)
    {
        $em = $this->getDoctrine()->getManager();
        $bip = $em->getRepository('AppBundle:Bip')->find($bip);

        return $this->render('bip/index.html.twig', array(
            'bip'=>$bip,
        ));
    }

    /**
     * @Route("/bip/{bip}/menu", name="menu")
     */
    public function menuAction($bip){
        $em = $this->getDoctrine()->getManager();
        $bip = $em->getRepository('AppBundle:Bip')->find($bip);

        $submenus = $em->getRepository('AppBundle:Submenu')->findByBip($bip);

        foreach($submenus as $k=>$v){
            $articles = $em->getRepository('AppBundle:Article')->findByMenu($v);
            $submenus[$k]->setArticles($articles);
            $articles = $submenus[$k]->getArticles();
            foreach($articles as $j=>$w){
                print $articles[$j]->getId();
            }
//            print $submenus[$k]->getId();
        }

        return $this->render('bip/leftmenu.html.twig', array(
            'submenu'=>$submenus,
        ));
    }
}
