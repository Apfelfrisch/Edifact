<?php

namespace Proengeno\Edifact\Test;

use Mockery as m;
use Proengeno\Edifact\Configuration;
use Proengeno\Edifact\Describer;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function getConfiguration()
    {
        $configuration = new Configuration;
        $configuration->addMessageDescription(__DIR__ . '/data/message_description.php', ['UNH' => '/./']);

        return $configuration;
    }

    public function getDescriber()
    {
        return Describer::build(__dir__ . '/data/message_description.php');
    }
}
