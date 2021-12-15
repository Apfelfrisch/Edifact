<?php

namespace Apfelfrisch\Edifact\Test\Validation;

use Apfelfrisch\Edifact\Message;
use Apfelfrisch\Edifact\SegmentFactory;
use Apfelfrisch\Edifact\Test\TestCase;
use Apfelfrisch\Edifact\Validation\Failure;
use Apfelfrisch\Edifact\Validation\Validator;

class ValidatorTest extends TestCase
{
    public function setUp(): void
    {
        TestSegment::$ruleOne = null;
        TestSegment::$ruleTwo = null;
    }

    /** @test */
    public function test_validate_unkown_elements(): void
    {
        TestSegment::$ruleOne = 'M|n|1';

        $validator = new Validator;

        $this->assertFalse($validator->isValid($this->buildMessage('1+unkown-element:unkown-component')));

        $this->assertInstanceOf(Failure::class, $failure = $validator->getFirstFailure());
        $this->assertSame(2, $failure->getElementPosition());
        $this->assertSame('unkown-element', $failure->getValue());
        $this->assertSame('The input Element is unkown', $failure->getMessage());
    }

    /** @test */
    public function test_validate_unkown_components(): void
    {
        TestSegment::$ruleOne = 'M|n|1';

        $validator = new Validator;

        $this->assertFalse($validator->isValid($this->buildMessage('1::unkown-component')));

        $this->assertInstanceOf(Failure::class, $failure = $validator->getFirstFailure());

        $this->assertSame(1, $failure->getElementPosition());
        $this->assertSame(2, $failure->getComponentPosition());
        $this->assertSame('unkown-component', $failure->getValue());
        $this->assertSame('The input Component is unkown', $failure->getMessage());
    }

    /** @test */
    public function test_validate_digit_values(): void
    {
        TestSegment::$ruleOne = 'M|n|..11';
        $digits = implode(range(0, 9));

        $validator = new Validator;

        $this->assertTrue($validator->isValid($this->buildMessage($digits)));
        $this->assertFalse($validator->isValid($this->buildMessage($digits . 'A')));
        $this->assertInstanceOf(Failure::class, $failure = $validator->getFirstFailure());
        $this->assertSame('TST', $failure->getSegmentName());
        $this->assertSame(1, $failure->getElementPosition());
        $this->assertSame(0, $failure->getComponentPosition());
        $this->assertSame($digits . 'A', $failure->getValue());
        $this->assertSame('String must contain only digits', $failure->getMessage());
    }

    /** @test */
    public function test_validate_alpha_values(): void
    {
        TestSegment::$ruleOne = 'M|a|..53';
        $alphaValues = implode(array_merge(range('a', 'z'), range('A', 'Z')));

        $validator = new Validator;

        $this->assertTrue($validator->isValid($this->buildMessage($alphaValues)));
        $this->assertFalse($validator->isValid($this->buildMessage($alphaValues . '1')));
        $this->assertInstanceOf(Failure::class, $failure = $validator->getFirstFailure());
        $this->assertSame($alphaValues . '1', $failure->getValue());
        $this->assertSame("String must contain only alphabetic characters", $failure->getMessage());
    }

    /** @test */
    public function test_validate_max_lenght(): void
    {
        TestSegment::$ruleOne = 'M|an|..2';

        $validator = new Validator;

        $this->assertTrue($validator->isValid($this->buildMessage('A')));

        $this->assertFalse($validator->isValid($this->buildMessage('ABC')));
        $this->assertInstanceOf(Failure::class, $failure = $validator->getFirstFailure());
        $this->assertSame('ABC', $failure->getValue());
        $this->assertSame("String is more than 2 characters long", $failure->getMessage());
    }

    /** @test */
    public function test_validate_needed_component(): void
    {
        TestSegment::$ruleOne = 'M|an|..2';

        $validator = new Validator;

        $this->assertTrue($validator->isValid($this->buildMessage('A')));

        $this->assertFalse($validator->isValid($this->buildMessage('')));
        $this->assertInstanceOf(Failure::class, $failure = $validator->getFirstFailure());
        $this->assertSame('', $failure->getValue());
        $this->assertSame("Component with Id [1:1] is missing", $failure->getMessage());
    }

    /** @test */
    public function test_validate_exact_string_lenght(): void
    {
        TestSegment::$ruleOne = 'M|an|3';

        $string = '123';

        $validator = new Validator;

        $this->assertTrue($validator->isValid($this->buildMessage('123')));

        $this->assertFalse($validator->isValid($this->buildMessage('12')));
        $this->assertInstanceOf(Failure::class, $failure = $validator->getFirstFailure());
        $this->assertSame('12', $failure->getValue());
        $this->assertSame("String is not 3 characters long", $failure->getMessage());

        $this->assertFalse($validator->isValid($this->buildMessage($string . '4')));
        $this->assertInstanceOf(Failure::class, $failure = $validator->getFirstFailure());
        $this->assertSame($string . '4', $failure->getValue());
        $this->assertSame("String is not 3 characters long", $failure->getMessage());
    }

    /** @test */
    public function test_validate_missing_optional_element(): void
    {
        $validator = new Validator;

        TestSegment::$ruleOne = 'O|an|3';
        $segmentFactory = new SegmentFactory;
        $segmentFactory->addSegment('TST', TestSegment::class);

        $message = Message::fromString('TST', $segmentFactory);

        $this->assertTrue($validator->isValid($message));
    }

    /** @test */
    public function test_validate_missing_required_element(): void
    {
        $validator = new Validator;

        TestSegment::$ruleOne = 'M|an|3';
        $segmentFactory = new SegmentFactory;
        $segmentFactory->addSegment('TST', TestSegment::class);

        $message = Message::fromString('TST', $segmentFactory);

        $this->assertFalse($validator->isValid($message));
        $this->assertInstanceOf(Failure::class, $failure = $validator->getFirstFailure());
        $this->assertSame(Failure::MISSING_COMPONENT, $failure->getType());
    }

    /** @test */
    public function test_validate_missing_optional_component(): void
    {
        $validator = new Validator;

        TestSegment::$ruleOne = 'M|an|3';
        TestSegment::$ruleTwo = 'O|an|3';

        $segmentFactory = new SegmentFactory;
        $segmentFactory->addSegment('TST', TestSegment::class);

        $message = Message::fromString('TST+ABC', $segmentFactory);

        $this->assertTrue($validator->isValid($message));
    }

    /** @test */
    public function test_validate_missing_required_component(): void
    {
        $validator = new Validator;

        TestSegment::$ruleOne = 'M|an|3';
        TestSegment::$ruleTwo = 'M|an|3';

        $segmentFactory = new SegmentFactory;
        $segmentFactory->addSegment('TST', TestSegment::class);

        $message = Message::fromString('TST+ABC', $segmentFactory);

        $this->assertFalse($validator->isValid($message));
        $this->assertInstanceOf(Failure::class, $failure = $validator->getFirstFailure());
        $this->assertSame(Failure::MISSING_COMPONENT, $failure->getType());
    }

    /** @test */
    public function test_validate_missing_valid_dependend_component(): void
    {
        $validator = new Validator;

        TestSegment::$ruleOne = 'D|an|3';
        TestSegment::$ruleTwo = 'O|an|3';

        $segmentFactory = new SegmentFactory;
        $segmentFactory->addSegment('TST', TestSegment::class);

        $message = Message::fromString('TST', $segmentFactory);

        $this->assertTrue($validator->isValid($message));
    }

    /** @test */
    public function test_validate_missing_invalid_dependend_component(): void
    {
        $validator = new Validator;

        TestSegment::$ruleOne = 'D|an|3';
        TestSegment::$ruleTwo = 'O|an|3';

        $segmentFactory = new SegmentFactory;
        $segmentFactory->addSegment('TST', TestSegment::class);

        $message = Message::fromString('TST+:ABC', $segmentFactory);

        $this->assertFalse($validator->isValid($message));
        $this->assertInstanceOf(Failure::class, $failure = $validator->getFirstFailure());
        $this->assertSame(Failure::MISSING_COMPONENT, $failure->getType());
    }

    /** @test */
    public function test_ignore_optional_empty_components(): void
    {
        TestSegment::$ruleOne = 'O|an|3';

        $validator = new Validator;

        $this->assertTrue($validator->isValid($this->buildMessage('')));
        $this->assertFalse($validator->isValid($this->buildMessage('1234')));
    }

    /** @test */
    public function test_iterate_over_failures(): void
    {
        TestSegment::$ruleOne = 'M|n|1';

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
