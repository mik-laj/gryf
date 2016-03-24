<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Entity\File;
use AppBundle\Entity\Log;
use AppBundle\Entity\Submenu;
use AppBundle\Exception\BIPNotFoundException;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use UserBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class BIPModController extends Controller
{

    /**
     * @Route("/admin/add/art/", name="admin_add_art")
     */
    public function adminAddArtAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $BIPManager = $this->get('bip_manager');
        try {
            $bip = $BIPManager->getCurrentBIP();
            $BIPManager->checkAdmin($bip, $this->getUser());
        }catch(BIPNotFoundException $e){
            return $e->redirectResponse;
        }

        $article = new Article();
        $article->setCreated(new \DateTime(date('Y-m-d H:i:s')));
        $article->setAuthor($this->getUser());
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
            $this->addFlash('notice', 'Pomyślnie dodano artykuł do menu.');
            $em->flush();

        }

        return $this->render('user/add_article.html.twig', array(
            'form'=>$form->createView(),
            'bip' => $bip,
        ));
    }

    /**
     * @Route("/admin/view/art/", name="admin_view_art")
     */
    public function adminViewArtAction()
    {
        $em = $this->getDoctrine()->getManager();
        $BIPManager = $this->get('bip_manager');
        try {
            $bip = $BIPManager->getCurrentBIP();
            $BIPManager->checkAdmin($bip, $this->getUser());
        }catch(BIPNotFoundException $e){
            return $e->redirectResponse;
        }
        $articles = $em->getRepository("AppBundle:Article");
        $qb = $articles->createQueryBuilder('a');
        $articles1 = $qb
            ->innerJoin('a.menu', 'm')
            ->innerJoin('m.bip', 'b')
//                    ->innerJoin('a.section', 's')
//                    ->innerJoin('s.menu','z')
//                    ->innerJoin('z.bip', 'y')
            ->where('b.id='.$bip->getId())->getQuery()->getResult();
        $qb2 = $articles->createQueryBuilder('s');
        $sections = $qb2
            ->innerJoin('s.section', 'x')
            ->innerJoin('x.menu', 'y')
            ->innerJoin('y.bip', 'p')
            ->where('p.id='.$bip->getId())->getQuery()->getResult();

        return $this->render('user/view_articles.html.twig', array(
            'bip' => $bip,
            'articles' => $articles1,
            'sections' => $sections,
        ));
    }

    /**
     * @Route("/admin/edit/{art}/", name="admin_edit_art")
     */
    public function adminEditArtAction(Request $request, $art)
    {
        $em = $this->getDoctrine()->getManager();
        $BIPManager = $this->get('bip_manager');
        try {
            $bip = $BIPManager->getCurrentBIP();
        } catch (BIPNotFoundException $e) {
            return $e->redirectResponse;
        }
        $article = $em->getRepository("AppBundle:Article")->find($art);

        $log = new Log();
        $log->setArticle($art);
        $log->setEditor($this->getUser());
        $log->setEdited(new \DateTime(date('Y-m-d H:i:s')));

        $form = $this->createFormBuilder($article)
            ->add('title')
            ->add('menu', EntityType::class, array(
                'class' => 'AppBundle:Submenu',
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) use ($bip) {
                    return $er->createQueryBuilder('s')
                        ->where("s.bip= " . $bip->getId());
                },
            ))
            ->add('content', TextareaType::class)
            ->add('save', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em->persist($log);
            $this->addFlash('notice', "Pomyślnie zaktualizowano artykuł.");
            $em->flush();
        }




        return $this->render('user/edit_art_menu.html.twig', array(
            'bip' => $bip,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/admin/remove/{art}/", name="admin_remove_art")
     */
    public function adminRemoveArtAction(Request $request, $art)
    {
        $em = $this->getDoctrine()->getManager();
        $BIPManager = $this->get('bip_manager');
        try {
            $bip = $BIPManager->getCurrentBIP();
        } catch (BIPNotFoundException $e) {
            return $e->redirectResponse;
        }
        $article = $em->getRepository("AppBundle:Article")->find($art);
        $em->remove($article);
        $em->flush();

        return $this->redirectToRoute('admin_view_art', array(
            'bip' => $bip->getId(),
        ));
    }

    /**
     * @Route("/admin/art/{art}/", name="admin_view_article")
     */
    public function adminArtViewAction($art)
    {
        $em = $this->getDoctrine()->getManager();
        $BIPManager = $this->get('bip_manager');
        try {
            $bip = $BIPManager->getCurrentBIP();
        } catch (BIPNotFoundException $e) {
            return $e->redirectResponse;
        }
        $article = $em->getRepository("AppBundle:Article")->find($art);
        $sec = $em->getRepository("AppBundle:Article");
        $qb = $sec->createQueryBuilder('a');
        $sections = $qb
            ->innerJoin('a.section', 's')
            ->where('s.id=' . $art)->getQuery()->getResult();


        return $this->render('user/view_art.html.twig', array(
            'bip' => $bip,
            'article' => $article,
            'sections' => $sections,
        ));
    }

    /**
     * @Route("/admin/sec/{art}/", name="admin_add_section")
     */
    public function adminAddSectionAction(Request $request, $art)
    {
        $em = $this->getDoctrine()->getManager();
//        $bip = $em->getRepository("AppBundle:Bip")->find($bip);
        $BIPManager = $this->get('bip_manager');
        try {
            $bip = $BIPManager->getCurrentBIP();
        } catch (BIPNotFoundException $e) {
            return $e->redirectResponse;
        }
        $art = $em->getRepository("AppBundle:Article")->find($art);
        $article = new Article();
        $article->setSection($art);
        $article->setCreated(new \DateTime(date('Y-m-d H:i:s')));
        $article->setAuthor($this->getUser());
        $form = $this->createFormBuilder($article)
            ->add('title')
            ->add('content', TextareaType::class)
            ->add('save', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em->persist($article);
            $this->addFlash('success', 'Pomyślnie dodano artykuł do sekcji.');
            $em->flush();

        }

        return $this->render('user/add_section.html.twig', array(
            'form' => $form->createView(),
            'bip' => $bip,
        ));
    }

    /**
     * @Route("/admin/dane/static/", name="admin_static_dane")
     */
    public function adminStaticDaneEditAction()
    {
        $em = $this->getDoctrine()->getManager();
        $BIPManager = $this->get('bip_manager');
        try {
            $bip = $BIPManager->getCurrentBIP();
        } catch (BIPNotFoundException $e) {
            return $e->redirectResponse;
        }

        return $this->render('user/edit_static.html.twig', array(

        ));
    }


}