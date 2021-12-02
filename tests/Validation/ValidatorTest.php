<?php

namespace Apfelfrisch\Edifact\Test\Validation;

use Apfelfrisch\Edifact\Message;
use Apfelfrisch\Edifact\Test\TestCase;
use Apfelfrisch\Edifact\Validation\Failure;
use Apfelfrisch\Edifact\Validation\Validator;

class ValidatorTest extends TestCase
{
    /** @test */
    public function test_validate_digit_values()
    {
        $validator = new Validator;

        $this->assertTrue(
            $validator->isValid(Message::fromString('EQD+AN+1234567890'))
        );

        $this->assertFalse(
            $validator->isValid(Message::fromString('EQD+AN+6b'))
        );

        $failure = $validator->getFirstFailure();

        $this->assertSame('EQD', $failure->segmentName);
        $this->assertSame('C237', $failure->elementKey);
        $this->assertSame('8260', $failure->componentKey);
        $this->assertSame('6b', $failure->value);
        $this->assertSame('The input must contain only digits', $failure->message);
    }

    /** @test */
    public function test_validate_alpha_values()
    {
        $validator = new Validator;

        $this->assertTrue($validator->isValid(Message::fromString('UCS+ABCDEF+GH')));

        $this->assertFalse($validator->isValid(Message::fromString('UCS+a15+GH')));

        $failure = $validator->getFirstFailure();

        $this->assertSame('UCS', $failure->segmentName);
        $this->assertSame('0096', $failure->elementKey);
        $this->assertSame('0096', $failure->componentKey);
        $this->assertSame('a15', $failure->value);
        $this->assertSame("The input does not match against pattern '/^[A-Za-z]*$/'", $failure->message);
    }

    /** @test */
    public function test_iterate_over_failures()
    {
        $validator = new Validator;

        $validator->isValid(Message::fromString("EQD+AN+6b'UCS+a15+GH'"));

        $i = 0;
        foreach ($validator->getFailures() as $failure) {
            $i++;
            $this->assertInstanceOf(Failure::class, $failure);
        }

        $this->assertSame(2, $i);
    }
}
