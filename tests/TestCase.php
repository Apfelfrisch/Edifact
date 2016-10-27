<?php

namespace Proengeno\Edifact\Test;

use Mockery as m;
use Proengeno\Edifact\Configuration;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function getConfiguration()
    {
        $configuration = new Configuration;
        $configuration->setSegmentNamespace('\Proengeno\Edifact\Test\Fixtures\Segments');
        $configuration->setExportSender('from');

        return $configuration;
    }
}
