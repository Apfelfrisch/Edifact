<?php

namespace Proengeno\Edifact\Test\Fixtures;

use DateTime;
use Proengeno\Edifact\Templates\AbstractBuilder;
use Proengeno\Edifact\Test\Fixtures\Segments\Unb;

class Builder extends AbstractBuilder
{
    protected $edifactClass = Message::class;

    const MESSAGE_TYPE = 'RANDOM_MESSAGE';
    const MESSAGE_SUBTYPE = 'VL';

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
        $this->writeSeg('unh', [
            $this->unbReference(),
            'REMADV',
            'D',
            '05A',
            'UN',
            '2.7b'
        ]);
    }

    protected function writeUnb()
    {
        return $this->writeSeg('unb', [
            'UNOC', 3, $this->from, 500, $this->to, 500, new DateTime(), $this->unbReference(), self::MESSAGE_SUBTYPE
        ]);
    }
}
