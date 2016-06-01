<?php

namespace Proengeno\Edifact\Test\Fixtures;

use Proengeno\Edifact\Message\Message as MessageCore;

class Message extends MessageCore
{
    protected static $builderClass = Builder::class;
    protected static $segments = [
        'BGM' => \Proengeno\Edifact\Test\Fixtures\Segments\Bgm::class,
        'DTM' => \Proengeno\Edifact\Test\Fixtures\Segments\Dtm::class,
        'LIN' => \Proengeno\Edifact\Test\Fixtures\Segments\Lin::class,
        'RFF' => \Proengeno\Edifact\Test\Fixtures\Segments\Rff::class,
        'UNA' => \Proengeno\Edifact\Test\Fixtures\Segments\Una::class,
        'UNB' => \Proengeno\Edifact\Test\Fixtures\Segments\Unb::class,
        'UNH' => \Proengeno\Edifact\Test\Fixtures\Segments\Unh::class,
        'UNT' => \Proengeno\Edifact\Test\Fixtures\Segments\Unt::class,
        'UNS' => \Proengeno\Edifact\Test\Fixtures\Segments\Uns::class,
        'UNZ' => \Proengeno\Edifact\Test\Fixtures\Segments\Unz::class,
    ];
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
}
    
