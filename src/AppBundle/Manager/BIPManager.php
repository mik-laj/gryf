<?php
namespace AppBundle\Manager;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Exception\BIPNotFoundException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class BIPManager
{
    private $currentBIP;

    private $router;

    private $homepage;

    private $em;

    private $user;

    public function __construct($router, EntityManager $entityManager, TokenStorage $token)
    {
        $this->router = $router;
        $this->homepage = 'homepage';
        $this->em = $entityManager;

    }

    public function getCurrentBIP(){
        if($this->currentBIP==null){
            $url = $this->router->generate($this->homepage);
            $redirect = new RedirectResponse($url);

            $exception = new BIPNotFoundException();
            $exception->redirectResponse = $redirect;
            throw $exception;
        }
        return $this->currentBIP;
    }

    public function checkAdmin($bip, $user_trying){
        $em = $this->em;
        if(!$this->isAdmin($bip, $user_trying)) {
            throw new AccessDeniedException();
        }
    }

    public function isAdmin($bip, $user_trying){
        $em = $this->em;
        $bip_admins = $em->getRepository('UserBundle:User')->findByBip($bip);

        if(in_array($user_trying, $bip_admins)) {
            return true;
        }
        return false;
    }

    public function setCurrentBIP($currentBIP){
        $this->currentBIP = $currentBIP;
    }
}