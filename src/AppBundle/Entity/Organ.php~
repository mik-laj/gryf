<?php
// src/AppBundle/Entity/User.php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="organs")
 */
class Organ
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
    protected $organ;

    /**
     * @ORM\ManyToOne(targetEntity="Bip")
     * @ORM\JoinColumn(name="bip", referencedColumnName="id")
     */
    protected $bip;

    protected $members;

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
     * Set organ
     *
     * @param string $organ
     * @return Organ
     */
    public function setOrgan($organ)
    {
        $this->organ = $organ;

        return $this;
    }

    /**
     * Get organ
     *
     * @return string 
     */
    public function getOrgan()
    {
        return $this->organ;
    }

    /**
     * Set bip
     *
     * @param \AppBundle\Entity\Bip $bip
     * @return Organ
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

    public function setMembers($members){
        $this->members = $members;
        return $this;
    }

    public function getMembers(){
        return $this->members;
    }
}
