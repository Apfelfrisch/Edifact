<?php

namespace Proengeno\Edifact\Test;

use Mockery as m;
use Proengeno\Edifact\Configuration;
use Proengeno\Edifact\Message\Describer;

abstract class TestCase extends \PHPUnit\Framework\TestCase
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
        $configuration->addMessageDescription(__DIR__ . '/data/message_description.php', ['UNH' => '/./']);

        return $configuration;
    }

    public function getDescriber()
    {
        return Describer::build(__dir__ . '/data/message_description.php');
    }
}
