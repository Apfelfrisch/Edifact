<?php 

namespace Proengeno\Edifact\Message\Messages;

use Message\Builder\Orders_17103_Builder;
use Proengeno\Edifact\Message\Message;

class Orders_17103 extends Message 
{
    protected static $validationBlueprint = [
        0 => ['name' => 'UNA'],
        1 => ['name' => 'UNH', 'maxLoops' => 10, 'necessity' => 'R', 'segments' => [
            0 => ['name' => 'BGM'],
            1 => ['name' => 'DTM'],
            2 => ['name' => 'IMD'],
            3 => ['name' => 'IMD'],
            4 => ['name' => 'RFF'],
            5 => ['name' => 'NAD'],
            6 => ['name' => 'NAD'],
            7 => ['name' => 'NAD'],
            8 => ['name' => 'LOC'],
            9 => ['name' => 'LIN', 'maxLoops' => 1, 'necessity' => 'R', 'segments' => [
                0 => ['name' => 'DTM'],
                1 => ['name' => 'DTM'],
            ]],
            10 => ['name' => 'UNS'],
            11 => ['name' => 'UNT'],
        ]],
        2 => ['name' => 'UNZ']
    ];

    public static function build($from, $to)
    {
        return new Orders_17103_Builder($from, $to);
    }
    
}
