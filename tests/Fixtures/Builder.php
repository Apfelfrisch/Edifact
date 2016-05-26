<?php

namespace Proengeno\Edifact\Test\Fixtures;

use DateTime;
use Proengeno\Edifact\Test\Fixtures\Segments\Unb;
use Proengeno\Edifact\Message\Builder as BuilderCore;

class Builder extends BuilderCore
{
    const MESSAGE_TYPE = 'RANDOM_MESSAGE';
    const MESSAGE_SUBTYPE = 'VL';

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

    protected function writeMessage($array)
    {
        $this->writeSegment($this->getSeg('unh')::fromAttributes(
            $this->unbReference(), 
            'REMADV',
            'D',
            '05A', 
            'UN',
            '2.7b'
        ));
    }

    protected function getUnb()
    {
        return Unb::fromAttributes(
            'UNOC', 3, $this->from, 500, $this->to, 500, new DateTime(), $this->unbReference(), self::MESSAGE_SUBTYPE
        );
    }

}
