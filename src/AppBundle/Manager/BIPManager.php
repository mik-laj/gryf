<?php
namespace AppBundle\Manager;

use AppBundle\Entity\Bip;

class BIPManager
{
    private $currentBIP;

    public function getCurrentBIP(){
        return $this->currentBIP;
    }

    public function setCurrentBIP(Bip $currentBIP){
        $this->currentBIP = $currentBIP;
    }
}