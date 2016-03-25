<?php
namespace AppBundle\EventListener;

use AppBundle\Manager\BIPManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Created by PhpStorm.
 * User: spake
 * Date: 14.03.16
 * Time: 16:07
 */
class CurrentBIPListener
{
    private $BIPManager;

    private $em;

    private $baseHost;

    public function __construct(BIPManager $BIPManager, EntityManager $em, $baseHost)
    {
        $this->BIPManager = $BIPManager;
        $this->em = $em;
        $this->baseHost = $baseHost;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $currentHost = $request->getHttpHost();
        $sub = str_replace('.'.$this->baseHost, '', $currentHost);

        if($sub==$currentHost){
            $this->BIPManager->setCurrentBIP(null);
        }else {
            $BIP = $this->em
                ->getRepository('AppBundle:Bip')
                ->findOneBy(array('url' => $sub));
            if (!$BIP) {
                $this->BIPManager->setCurrentBIP(null);
            }else {
                $this->BIPManager->setCurrentBIP($BIP);
            }
        }
    }
}