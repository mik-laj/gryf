<?php
namespace AppBundle\EventListener;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RegisterListener implements EventSubscriberInterface
{
    protected $em;

    /*
     * we need an EntityManager to create some convenience for you,
     * so please be so kind and provide us one :-)
     */
    function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::REGISTRATION_SUCCESS => 'onRegistrationConfirmed',
        );
    }

    public function onRegistrationConfirmed(FormEvent $event)
    {
        $user = $event->getForm()->getData();

        print $user->getId();
        die();

//        $this->em->flush();
    }
}