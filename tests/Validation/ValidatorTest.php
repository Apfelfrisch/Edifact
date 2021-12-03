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

        $this->assertNull(
            $validator->validateUntilFirstFailure(Message::fromString('EQD+AN+1234567890'))
        );

        $failure = $validator->validateUntilFirstFailure(Message::fromString('EQD+AN+6b'));

        $this->assertSame('EQD', $failure->segmentName);
        $this->assertSame('C237', $failure->elementKey);
        $this->assertSame('8260', $failure->componentKey);
        $this->assertSame('6b', $failure->value);
        $this->assertSame('The input must contain only digits', $failure->message);
    }

    /** @test */
    public function test_iterate_over_failures()
    {
        $validator = new Validator;

        $results = $validator->validate(Message::fromString("EQD+AN+6b'UCS+a15+GH'"));

        $i = 0;
        foreach ($results as $result) {
            $i++;
            $this->assertInstanceOf(Failure::class, $result);
        }

        $this->assertSame($i, 2);
    }

    /** @test */
    public function test_validate_alph_values()
    {
        $validator = new Validator();

        $this->assertNull(
            $validator->validateUntilFirstFailure(Message::fromString('UCS+ABCDEF+GH'))
        );

        $failure = $validator->validateUntilFirstFailure(Message::fromString('UCS+a15+GH'));

        $this->assertSame('UCS', $failure->segmentName);
        $this->assertSame('0096', $failure->elementKey);
        $this->assertSame('0096', $failure->componentKey);
        $this->assertSame('a15', $failure->value);
        $this->assertSame("The input does not match against pattern '/^[A-Za-z]*$/'", $failure->message);
    }
}
