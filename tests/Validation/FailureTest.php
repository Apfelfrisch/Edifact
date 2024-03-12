<?php

namespace Apfelfrisch\Edifact\Test\Validation;

use Apfelfrisch\Edifact\Test\TestCase;
use Apfelfrisch\Edifact\Validation\Failure;

class FailureTest extends TestCase
{
    public function test_instantiate_with_default_message_and_unh_count(): void
    {
        $failure = new Failure(Failure::MISSING_COMPONENT, 'TST', 5, 10, 'TestValue', 'TestText');

        $this->assertSame(Failure::MISSING_COMPONENT, $failure->getType());
        $this->assertSame('TST', $failure->getSegmentName());
        $this->assertSame(5, $failure->getElementPosition());
        $this->assertSame(10, $failure->getComponentPosition());
        $this->assertSame('TestValue', $failure->getValue());
        $this->assertSame('TestText', $failure->getMessage());
        $this->assertSame(0, $failure->getMessageCounter());
        $this->assertSame(0, $failure->getUnhCounter());
    }

    public function test_setting_message_counter(): void
    {
        $failure = new Failure(Failure::MISSING_COMPONENT, 'TST', 5, 10, 'TestValue', 'TestText');

        $this->assertSame(0, $failure->getMessageCounter());

        $failure->setMessageCounter(2);

        $this->assertSame(2, $failure->getMessageCounter());
    }

    public function test_setting_unh_counter(): void
    {
        $failure = new Failure(Failure::MISSING_COMPONENT, 'TST', 5, 10, 'TestValue', 'TestText');

        $this->assertSame(0, $failure->getUnhCounter());

        $failure->setUnhCounter(5);

        $this->assertSame(5, $failure->getUnhCounter());
    }
}
