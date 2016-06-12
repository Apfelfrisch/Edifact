<?php

namespace Proengeno\Edifact\Test\Fixtures;

use Proengeno\Edifact\Message\EdifactFile;
use Proengeno\Edifact\Templates\AbstractMessage;

class Message extends AbstractMessage
{
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

    public static function fromString($string)
    {
        $file = new EdifactFile('php://temp', 'w+');
        $file->writeAndRewind($string);
        return new static($file);
    }

    public function getValidationBlueprint()
    {
        return [
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

    public function testConfiguration()
    {
        return $this->configuration['test']();
    }
}
    
