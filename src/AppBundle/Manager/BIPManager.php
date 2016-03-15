<?php
namespace AppBundle\Manager;

use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Exception\BIPNotFoundException;

class BIPManager
{
    private $currentBIP;

    private $router;

    private $homepage;

    public function __construct($router)
    {
        $this->router = $router;
        $this->homepage = 'homepage';
    }

    public function getCurrentBIP(){
        if($this->currentBIP==null){
            $url = $this->router->generate($this->homepage);
            $redirect = new RedirectResponse($url);

            $exception = new BIPNotFoundException();
            $exception->redirectResponse = $redirect;
            $exception->test = "HEHE";
            throw $exception;
        }
        return $this->currentBIP;
    }

    public function setCurrentBIP($currentBIP){
        $this->currentBIP = $currentBIP;
    }
}