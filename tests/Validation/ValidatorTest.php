<?php

namespace Apfelfrisch\Edifact\Test\Validation;

use Apfelfrisch\Edifact\Message;
use Apfelfrisch\Edifact\SegmentFactory;
use Apfelfrisch\Edifact\Test\TestCase;
use Apfelfrisch\Edifact\Validation\Failure;
use Apfelfrisch\Edifact\Validation\Validator;

class ValidatorTest extends TestCase
{
    /** @test */
    public function test_validate_unkown_elements()
    {
        TestSegment::$rule = 'M|n|1';

        $validator = new Validator;

        $this->assertFalse($validator->isValid($this->buildMessage('1+unkown-element:unkown-component')));

        $failure = $validator->getFirstFailure();
        $this->assertSame(2, $failure->getElementPosition());
        $this->assertSame('unkown-element', $failure->getValue());
        $this->assertSame('The input Element is unkown', $failure->getMessage());
    }

    /** @test */
    public function test_validate_unkown_components()
    {
        TestSegment::$rule = 'M|n|1';

        $validator = new Validator;

        $this->assertFalse($validator->isValid($this->buildMessage('1:unkown-component')));

        $failure = $validator->getFirstFailure();
        $this->assertSame(1, $failure->getElementPosition());
        $this->assertSame(1, $failure->getComponentPosition());
        $this->assertSame('unkown-component', $failure->getValue());
        $this->assertSame('The input Component is unkown', $failure->getMessage());
    }

    /** @test */
    public function test_validate_digit_values()
    {
        TestSegment::$rule = 'M|n|..11';
        $digits = implode(range(0, 9));

        $validator = new Validator;

        $this->assertTrue($validator->isValid($this->buildMessage($digits)));
        $this->assertFalse($validator->isValid($this->buildMessage($digits . 'A')));
        $failure = $validator->getFirstFailure();
        $this->assertSame('TST', $failure->getSegmentName());
        $this->assertSame(1, $failure->getElementPosition());
        $this->assertSame(0, $failure->getComponentPosition());
        $this->assertSame($digits . 'A', $failure->getValue());
        $this->assertSame('String must contain only digits', $failure->getMessage());
    }

    /** @test */
    public function test_validate_alpha_values()
    {
        TestSegment::$rule = 'M|a|..53';
        $alphaValues = implode(array_merge(range('a', 'z'), range('A', 'Z')));

        $validator = new Validator;

        $this->assertTrue($validator->isValid($this->buildMessage($alphaValues)));
        $this->assertFalse($validator->isValid($this->buildMessage($alphaValues . '1')));
        $failure = $validator->getFirstFailure();
        $this->assertSame($alphaValues . '1', $failure->getValue());
        $this->assertSame("String must contain only alphabetic characters", $failure->getMessage());
    }

    /** @test */
    public function test_validate_max_lenght()
    {
        TestSegment::$rule = 'M|an|..2';

        $validator = new Validator;

        $this->assertTrue($validator->isValid($this->buildMessage('A')));

        $this->assertFalse($validator->isValid($this->buildMessage('ABC')));
        $failure = $validator->getFirstFailure();
        $this->assertSame('ABC', $failure->getValue());
        $this->assertSame("String is more than 2 characters long", $failure->getMessage());
    }

    /** @test */
    public function test_validate_min_lenght()
    {
        TestSegment::$rule = 'M|an|..2';

        $validator = new Validator;

        $this->assertTrue($validator->isValid($this->buildMessage('A')));

        $this->assertFalse($validator->isValid($this->buildMessage('')));
        $failure = $validator->getFirstFailure();
        $this->assertSame('', $failure->getValue());
        $this->assertSame("String is less than 1 characters long", $failure->getMessage());
    }

    /** @test */
    public function test_validate_exact_string_lenght()
    {
        TestSegment::$rule = 'M|an|3';

        $string = '123';

        $validator = new Validator;

        $this->assertTrue($validator->isValid($this->buildMessage('123')));

        $this->assertFalse($validator->isValid($this->buildMessage('12')));
        $failure = $validator->getFirstFailure();
        $this->assertSame('12', $failure->getValue());
        $this->assertSame("String is not 3 characters long", $failure->getMessage());

        $this->assertFalse($validator->isValid($this->buildMessage($string . '4')));
        $failure = $validator->getFirstFailure();
        $this->assertSame($string . '4', $failure->getValue());
        $this->assertSame("String is not 3 characters long", $failure->getMessage());
    }

    /** @test */
    public function test_ignore_optional_empty_components()
    {
        TestSegment::$rule = 'O|an|3';

        $validator = new Validator;

        $this->assertTrue($validator->isValid($this->buildMessage('')));
        $this->assertFalse($validator->isValid($this->buildMessage('1234')));
    }

    /** @test */
    public function test_iterate_over_failures()
    {
        TestSegment::$rule = 'M|n|1';

        $validator = new Validator;

        $this->assertFalse($validator->isValid($this->buildMessage('AB')));

        $i = 0;
        foreach ($validator->getFailures() as $failure) {
            $i++;
            $this->assertInstanceOf(Failure::class, $failure);
        }

        $this->assertSame(2, $i);
    }

    private function buildMessage(string $string): Message
    {
        $segmentFactory = new SegmentFactory;
        $segmentFactory->addSegment('TST', TestSegment::class);

        return Message::fromString('TST+' . $string, $segmentFactory);
    }
}
