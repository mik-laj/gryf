<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Bip;
use AppBundle\Form\Type\BIPType;
use AppBundle\Form\Type\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\User;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig');
    }

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
