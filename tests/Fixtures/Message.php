<?php

use Proengeno\Edifact\Message\MessageCore;

class Message extends MessageCore
{
    protected static $validationBlueprint = [
        ['name' => 'UNA'],
        ['name' => 'UNH', 'maxLoops' => 10, 'necessity' => 'R', 'segments' => [
            ['name' => 'BGM', 'templates' => ['docCode' => ['7', '380']] ],
            ['name' => 'LIN', 'maxLoops' => 5, 'necessity' => 'R', 'segments' => [
                ['name' => 'DTM'],
            ]],
            ['name' => 'UNS'],
            ['name' => 'UNT'],
        ]],
        ['name' => 'UNZ']
    ];
}
    
