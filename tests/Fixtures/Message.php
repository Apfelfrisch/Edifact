<?php

namespace Proengeno\Edifact\Test\Fixtures;

use Proengeno\Edifact\Configuration;
use Proengeno\Edifact\Message\EdifactFile;
use Proengeno\Edifact\Templates\AbstractMessage;

class Message extends AbstractMessage
{
    protected static $blueprint = [
        ['name' => 'UNA'],
        ['name' => 'LOOP', 'maxLoops' => 10, 'necessity' => 'R', 'segments' => [
            ['name' => 'UNH'],
            ['name' => 'BGM', 'templates' => ['docCode' => ['7', '380']] ],
            ['name' => 'RFF', 'necessity' => 'O', 'templates' => ['code' => ['Z12']]],
            ['name' => 'RFF', 'necessity' => 'O', 'templates' => ['code' => ['Z13']]],
            ['name' => 'LOOP', 'maxLoops' => 10, 'necessity' => 'O', 'segments' => [
                ['name' => 'LIN'],
                ['name' => 'LOOP', 'maxLoops' => 10, 'necessity' => 'O', 'segments' => [
                    ['name' => 'DTM'],
                ]],
            ]],
            ['name' => 'LOOP', 'maxLoops' => 10, 'necessity' => 'O', 'segments' => [
                ['name' => 'UNS'],
            ]],
            ['name' => 'UNT', 'maxLoops' => 5],
        ]],
        ['name' => 'UNZ']
    ];

    public function testConfiguration()
    {
        return $this->configuration['test']();
    }
}
