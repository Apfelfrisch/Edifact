<?php

use Proengeno\Edifact\Message\MessageCore;

class Message extends MessageCore
{
    protected static $firstBodySegment = 'BGM';

    protected static $validationBlueprint = [
        0 => ['name' => 'UNA'],
        1 => ['name' => 'UNH', 'maxLoops' => 10, 'necessity' => 'R', 'segments' => [
            0 => ['name' => 'BGM', 'templates' => ['docCode' => ['7', '380']] ],
            1 => ['name' => 'LIN', 'maxLoops' => 1, 'necessity' => 'R', 'segments' => [
                0 => ['name' => 'DTM'],
            ]],
            2 => ['name' => 'UNS'],
            3 => ['name' => 'UNT'],
        ]],
        2 => ['name' => 'UNZ']
    ];
}
    
