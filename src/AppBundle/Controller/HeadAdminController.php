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


class HeadAdminController extends Controller
{
    /**
     * @Route("/master/", name="master_bip_list")
     */
    public function masterViewAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $bips = $em->getRepository("AppBundle:Bip");
        $qb = $bips->createQueryBuilder('bip');
        $query = $qb->getQuery();

        $paginator = $this->get('knp_paginator');
        $bips = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5
        );
        return $this->render('master/view_bips.html.twig', array(
            'bips'=>$bips,
        ));
    }

    /**
     * @Route("/master/edit/bip/{bip}/", name="master_edit_bip")
     */
    public function masterEditBipAction(Request $request, $bip)
    {
        $em = $this->getDoctrine()->getManager();
        $bip = $em->getRepository("AppBundle:Bip")->find($bip);

        $form = $this->createFormBuilder($bip)
            ->add('name')
            ->add('url')
            ->add('save', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if($form->isValid()){
            $this->addFlash('notice', "Pomyślnie zaktualizowano dane BIPu.");
            $em->flush();
            return $this->redirectToRoute('master_bip_list');
        }

        return $this->render('master/edit_bip.html.twig', array(
            'bip'=>$bip,
            'form'=>$form->createView(),
        ));
    }

    /**
     * @Route("/master/remove/bip/{bip}/", name="master_remove_bip")
     */
    public function masterRemoveBipAction(Request $request, $bip)
    {
        $em = $this->getDoctrine()->getManager();
        $bip = $em->getRepository("AppBundle:Bip")->find($bip);
        $bip_name = $bip->getName();
        $em->remove($bip);
        $em->flush();
        $this->addFlash('notice', "Pomyślnie usunięto BIP: ".$bip_name.".");

        return $this->redirectToRoute('master_bip_list');
    }
}
?>