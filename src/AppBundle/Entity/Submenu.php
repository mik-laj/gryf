<?php
// src/AppBundle/Entity/User.php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="submenus")
 */
class Submenu
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
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="Bip")
     * @ORM\JoinColumn(name="bip", referencedColumnName="id")
     */
    protected $bip;

    /**
     * @ORM\Column(type="integer")
     */
    protected $position;

    protected $articles;

    /**
     * @ORM\COlumn(type="integer")
     */
    protected $static;

    public function __construct()
    {

        // your own logic
        $this->static = 0;
    }


    public function getArticles(){
        return $this->articles;
    }

    public function setArticles($articles){
        $this->articles = $articles;
        return $this;
    }
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Submenu
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set bip
     *
     * @param \AppBundle\Entity\Bip $bip
     * @return Submenu
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

    /**
     * Set order
     *
     * @param integer $order
     * @return Submenu
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return integer 
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set position
     *
     * @param integer $position
     * @return Submenu
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer 
     */
    public function getPosition()
    {
        return $this->position;
    }
}
