<?php
namespace AppBundle\EventListener;

use AppBundle\Controller\BeforeUserController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class BeforeUserListener
{

    public function __construct($bipManager, $em)
    {
        $this->bipManager = $bipManager;
        $this->em = $em;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        /*
        * $controller passed can be either a class or a Closure.
        * This is not usual in Symfony but it may happen.
        * If it is a class, it comes in array format
        */
        if (!is_array($controller)) {
            return;
        }

        if ($controller[0] instanceof BeforeUserController) {
                $bip = $this->bipManager->getCurrentBIP();
                if(!$bip->getPublic()){
                    throw new AccessDeniedHttpException('BIP '.$bip->getName().' jest obecnie niedostępny!');
                }
        }
    }
}