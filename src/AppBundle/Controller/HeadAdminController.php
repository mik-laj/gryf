<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Entity\Submenu;
use UserBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
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
            10
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

    /**
     * @Route("/master/view/user/", name="master_user_list")
     */
    public function masterUserListAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository("UserBundle:User");
        $qb = $users->createQueryBuilder('user');
        $query = $qb->getQuery();

        $paginator = $this->get('knp_paginator');
        $users = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );
        return $this->render("master/view_users.html.twig", array(
            'users'=>$users,
        ));
    }

    /**
     * @Route("/master/edit/user/{user}/", name="master_edit_user")
     */
    public function masterUserEditAction(Request $request, $user)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository("UserBundle:User")->find($user);

        $form = $this->createFormBuilder($user)
            ->add('username')
            ->add('email')
            ->add('nazwisko')
            ->add('imie')
            ->add('save', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);

        if($form->isValid()){
            $this->addFlash('notice', "Pomyślnie zaktualizowano dane użytkownika.");
            $em->flush();
            return $this->redirectToRoute('master_user_list');
        }

        return $this->render('master/edit_user.html.twig', array(
            'user'=>$user,
            'form'=>$form->createView(),
        ));
    }
    /**
     * @Route("/master/user/password/{user}/", name="master_user_password_change")
     */
    public function adminUserPasswordChangeAction($user, Request $request)
    {
        $em=$this->getDoctrine()->getManager();
        $BIPManager = $this->get('bip_manager');
        $bip = $BIPManager->getCurrentBIP();
        $user=$em->getRepository("UserBundle:User")->find($user);

        $form = $this->createFormBuilder($user)
            ->add('plainPassword', RepeatedType::class, array(
                'type' => PasswordType::class,
                'first_options'  => array('label' => 'Nowe Hasło'),
                'second_options' => array('label' => 'Powtórz Hasło'),
            ))
            ->add('save', SubmitType::class, array('label'=>'Zapisz'))
            ->getForm();

        $form->handleRequest($request);
        if($form->isValid()){
            $this->addFlash('notice', "Pomyślnie zmieniono hasło użytkownika ".$user->getNazwisko()." ".$user->getImie());
            $em->flush();
            return $this->redirectToRoute('master_user_list');
        }

        return $this->render('master/edit_user_password.html.twig', array(
            'user'=>$user,
            'form'=>$form->createView(),
            'bip'=>$bip,
        ));
    }

    /**
     * @Route("/master/user/role/{user}/", name="master_user_role")
     */
    public function adminUserRoleAction($user)
    {
        $em=$this->getDoctrine()->getManager();
        $BIPManager = $this->get('bip_manager');
        $bip = $BIPManager->getCurrentBIP();
        $user=$em->getRepository("UserBundle:User")->find($user);
        $role = $user->getRoles();
        if(in_array("ROLE_BIPADMIN",$role))
        {
            $user->removeRole("ROLE_BIPADMIN");
            $em->persist($user);
            $em->flush();
            $this->addFlash('notice','Zdegradowano '.$user->getImie().' '.$user->getNazwisko().' na Moderatora');
        }
        else
        {
            $user->addRole('ROLE_BIPADMIN');
            $em->persist($user);
            $em->flush();
            $this->addFlash('notice','Awansowano '.$user->getImie().' '.$user->getNazwisko().' na Admina');
        }
        return $this->redirectToRoute('master_user_list');
    }

    /**
     * @Route("/master/remove/user/{user}/", name="master_remove_user")
     */
    public function masterRemoveUserAction(Request $request, $user)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository("UserBundle:User")->find($user);
        $username = $user->getUsername();
        $em->remove($user);
        $em->flush();
        $this->addFlash('notice', "Pomyślnie usunięto użytkownika: ".$username.".");

        return $this->redirectToRoute('master_user_list');
    }
}
?>