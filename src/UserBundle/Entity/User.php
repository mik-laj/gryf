<?php
// src/AppBundle/Entity/User.php

namespace UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Bip", cascade={"persist"})
     * @ORM\JoinColumn(name="bip", referencedColumnName="id")
     */
    protected $bip;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }

    /**
     * Set bip
     *
     * @param \AppBundle\Entity\Bip $bip
     * @return User
     */
    public function setBip(\AppBundle\Entity\Bip $bip = null)
    {
        $this->bip = $bip;

        return $this;
    }

    /**
     * Get bip
     *
     * @return \AppBundle\Entity\Bip 
     */
    public function getBip()
    {
        return $this->bip;
    }
}
