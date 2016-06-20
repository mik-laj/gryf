<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Bip;
use AppBundle\Exception\BIPNotFoundException;
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
        $BIPManager = $this->get('bip_manager');
        try {
            $bip = $BIPManager->getCurrentBIP();
        }catch(BIPNotFoundException $e){
            return $this->render('default/index.html.twig');
        }
        return $this->redirectToRoute('bip');
    }

    /**
     * @Route("/redirect", name="redirect_helper")
     */
    public function redirectHelperAction(Request $request)
    {
        $BIPManager = $this->get('bip_manager');

        $em = $this->getDoctrine()->getManager();
        return $BIPManager->redirectToBIP($this->getUser()->getBip(), 'admin_view_profile');
    }
}
