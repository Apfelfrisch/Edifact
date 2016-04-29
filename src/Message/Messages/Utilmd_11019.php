<?php 

namespace Proengeno\Edifact\Message\Messages;

use Proengeno\Edifact\Message\Message;

class Utilmd_11019 extends Message 
{
    protected static $validationBlueprint = [
        0 => ['name' => 'UNA'],
        1 => ['name' => 'UNH', 'maxLoops' => 10, 'necessity' => 'R', 'segments' => [
            0 => ['name' => 'BGM'],
            1 => ['name' => 'DTM'],
            2 => ['name' => 'DTM'],
            3 => ['name' => 'NAD'],
            4 => ['name' => 'NAD'],
            5 => ['name' => 'IDE', 'maxLoops' => 1000, 'necessity' => 'R', 'segments' => [
                0 => ['name' => 'IMD'],
                1 => ['name' => 'DTM'],
                2 => ['name' => 'DTM'],
            ]],
            6 => ['name' => 'UNT'],
        ]],
        2 => ['name' => 'UNZ']
    ];

    public static function build($from, $to)
    {
        return new Orders_17103Builder($from, $to);
    }
}
