<?php

namespace Proengeno\Edifact\Test\Fixtures;

use DateTime;
use Proengeno\Edifact\Message\Segments\Unb;
use Proengeno\Edifact\Message\Builder as BuilderCore;

class Builder extends BuilderCore
{
    protected $messageType = 'RANDOM_MESSAGE';
    protected $messageSubType = 'VL';

    private $energyType;

    /*
     * Methode nur zur Testzwecken.
     * Nicht in Produktion nutzten, da das Object nach Rückgabe noch 
     * zerstört werden kann.
     */
    public function getEdifactFile()
    {
        return $this->edifactFile;
    }

    protected function getMessage($array)
    {
        return null;
    }

    protected function getUnb()
    {
        return Unb::fromAttributes('UNOC', 3, $this->from, 500, $this->to, 500, new DateTime(), $this->unbReference(), $this->messageSubType);
    }

}
