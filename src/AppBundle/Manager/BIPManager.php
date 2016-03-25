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

    private $host;

    public function __construct($router, EntityManager $entityManager, TokenStorage $token, $host)
    {
        $this->router = $router;
        $this->homepage = 'homepage';
        $this->em = $entityManager;
        $this->host = $host;

    }

    public function getBIPUrl($bip=null){
        if($bip==!null) {
            $bip = $this->em->getRepository("AppBundle:Bip")->find($bip);
            $url = $bip->getUrl() . "." . $this->host;
        }else{
            $url = $this->getCurrentBIP()->getUrl().".".$this->host;
        }

        return $url;
    }

    public function redirectToBIP($bip, $route, $parameters=null){
        $bip = $this->em->getRepository("AppBundle:Bip")->find($bip);
        $url = "http://".$this->getBIPUrl($bip);
        $url .= $this->router->generate($route);

        $redirect = new RedirectResponse($url);

        return $redirect;
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