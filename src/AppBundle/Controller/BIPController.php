<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Bip;
use AppBundle\Entity\Submenu;
use AppBundle\Entity\Log;
use AppBundle\Exception\BIPNotFoundException;
use AppBundle\Exception\BIPNotFoundExceptionInterface;
use AppBundle\Form\Type\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use UserBundle\Entity\User;

class BIPController extends Controller
{

    /**
     * @Route("/download/{file}", name="file_download")
     */
    public function downloadFileAction(Request $request, $file)
    {
        $em = $this->getDoctrine()->getManager();
        $file_db = $em->getRepository('AppBundle:File')->find($file);
        $file = fopen($file_db->getWebPath(), 'r');

        $response = new Response();
        $response->headers->set('Content-type', 'application/octect-stream');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $file_db->getPath()));
        $response->headers->set('Content-Length', filesize($file_db->getWebPath()));
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->setContent(readfile($file_db->getWebPath()));

        return $response;

//        return $this->redirect("/".$file_db->getWebPath());
    }

    /**
     * @Route("/home", name="bip")
     */
    public function  bipAction()
    {
        $em = $this->getDoctrine()->getManager();
        $BIPManager = $this->get('bip_manager');
        try {
            $bip = $BIPManager->getCurrentBIP();
        }catch(BIPNotFoundException $e){
            return $e->redirectResponse;
        }

        $article = $em->getRepository("AppBundle:Article")->find($art);
        $sec = $em->getRepository("AppBundle:Article");
        $qb = $sec->createQueryBuilder('a');
        $sections = $qb
            ->innerJoin('a.section', 's')
            ->where('s.id='.$art)->getQuery()->getResult();
        $log = $em->getRepository("AppBundle:Log");
        $qb = $log->createQueryBuilder('a');
        $logs = $qb
            ->innerJoin('a.article', 'i')
            ->where('i.id='.$art)->getQuery()->getResult();
        $attachments = $em->getRepository('AppBundle:File')->findByArticle($art);


        return $this->render('bip/article.html.twig', array(
            'bip'=>$bip,
            'article'=>$article,
            'sections'=>$sections,
            'logs'=>$logs,
            'attachments'=>$attachments,
        ));

//        return $this->render('bip/index.html.twig', array(
//            'bip'=>$bip,
//        ));
    }

    /**
     * @Route("/menu", name="menu")
     */
    public function menuAction(){
        $em = $this->getDoctrine()->getManager();
        $BIPManager = $this->get('bip_manager');
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
//        $mustbe = $em->getRepository('AppBundle:Article')->findByBip($bip);

        return $this->render('bip/leftmenu.html.twig', array(
            'submenu'=>$submenus,
//            'mustbe'=>$mustbe,
        ));
    }

    /**
     * @Route("/art/add/", name="add_art")
     */
    public function addArtAction()
    {
        return $this->render('user/add_article.html.twig');
    }


    /**
     * @Route("/art/{art}/", name="art_view")
     */
    public function viewArtAction($art)
    {
        $em = $this->getDoctrine()->getManager();
        $BIPManager = $this->get('bip_manager');
        try {
            $bip = $BIPManager->getCurrentBIP();
        }catch(BIPNotFoundException $e){
            return $e->redirectResponse;
        }
        $article = $em->getRepository("AppBundle:Article")->find($art);
        $sec = $em->getRepository("AppBundle:Article");
        $qb = $sec->createQueryBuilder('a');
        $sections = $qb
            ->innerJoin('a.section', 's')
            ->where('s.id='.$art)->getQuery()->getResult();
        $log = $em->getRepository("AppBundle:Log");
        $qb = $log->createQueryBuilder('a');
        $logs = $qb
            ->innerJoin('a.article', 'i')
            ->where('i.id='.$art)->getQuery()->getResult();
        $attachments = $em->getRepository('AppBundle:File')->findByArticle($art);


        return $this->render('bip/article.html.twig', array(
            'bip'=>$bip,
            'article'=>$article,
            'sections'=>$sections,
            'logs'=>$logs,
            'attachments'=>$attachments,
        ));
    }

    /**
     * @Route("/all/", name="bips_view")
     */
    public function viewBipListAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $bip = $em->getRepository("AppBundle:Bip");
        $qb = $bip->createQueryBuilder('bip');
        $query = $qb->getQuery();

        $paginator = $this->get('knp_paginator');
        $bip = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('bip/bip_list.html.twig', array(
            'bips'=>$bip,
        ));
    }

    /**
     * @Route("/management/", name="management")
     */
    public function BIPManagamentAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $BIPManager = $this->get('bip_manager');
        try {
            $bip = $BIPManager->getCurrentBIP();
            $BIPManager->checkAdmin($bip, $this->getUser());
        }catch(BIPNotFoundException $e){
            return $e->redirectResponse;
        }

        $organy = $em->getRepository("AppBundle:Organ")->findByBip($bip);
        foreach($organy as $k=>$v){
            $organy[$k]->setMembers($em->getRepository('AppBundle:Member')->findByOrgan($v));
        }

        return $this->render('bip/zarzad.html.twig', array(
            'bip'=>$bip,
            'organy'=>$organy,
        ));
    }
}
