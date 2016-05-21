<?php

namespace Proengeno\Edifact\Test\Fixtures;

use Proengeno\Edifact\Message\Message as MessageCore;

class Message extends MessageCore
{
    protected static $validationBlueprint = [
        ['name' => 'UNA'],
        ['name' => 'UNH', 'maxLoops' => 10, 'necessity' => 'R', 'segments' => [
            ['name' => 'BGM', 'templates' => ['docCode' => ['7', '380']] ],
            ['name' => 'LIN', 'maxLoops' => 5, 'necessity' => 'R', 'segments' => [
                ['name' => 'DTM', 'maxLoops' => 5],
            ]],
            ['name' => 'UNS'],
            ['name' => 'UNT', 'maxLoops' => 5],
        ]],
        ['name' => 'UNZ']
    ];
    
    public static function build($from, $to)
    {
        return new Builder(static::class, $from, $to);
    }
}
    
