<?php
// src/AppBundle/Entity/User.php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="menu")
 */
class Menu
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $nazwa;

    /**
     * @ORM\ManyToOne(targetEntity="Bip")
     * @ORM\JoinColumn(name="bip", referencedColumnName="id")
     */
    protected $bip;

    /**
     *
     */

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }
}