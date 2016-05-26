<?php

namespace Proengeno\Edifact\Test;

use Mockery as m;
use Proengeno\Edifact\EdifactRegistrar;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        EdifactRegistrar::addSegement('BGM', 'Proengeno\Edifact\Test\Fixtures\Segments\Bgm');
        EdifactRegistrar::addSegement('DTM', 'Proengeno\Edifact\Test\Fixtures\Segments\Dtm');
        EdifactRegistrar::addSegement('LIN', 'Proengeno\Edifact\Test\Fixtures\Segments\Lin');
        EdifactRegistrar::addSegement('RFF', 'Proengeno\Edifact\Test\Fixtures\Segments\Rff');
        EdifactRegistrar::addSegement('UNA', 'Proengeno\Edifact\Test\Fixtures\Segments\Una');
        EdifactRegistrar::addSegement('UNB', 'Proengeno\Edifact\Test\Fixtures\Segments\Unb');
        EdifactRegistrar::addSegement('UNH', 'Proengeno\Edifact\Test\Fixtures\Segments\Unh');
        EdifactRegistrar::addSegement('UNT', 'Proengeno\Edifact\Test\Fixtures\Segments\Unt');
        EdifactRegistrar::addSegement('UNS', 'Proengeno\Edifact\Test\Fixtures\Segments\Uns');
        EdifactRegistrar::addSegement('UNZ', 'Proengeno\Edifact\Test\Fixtures\Segments\Unz');
    }

    protected function tearDown()
    {
        m::close();
    }
}
