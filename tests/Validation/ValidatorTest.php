<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Test\Validation;

use Apfelfrisch\Edifact\Exceptions\ValidationException;
use Apfelfrisch\Edifact\Message;
use Apfelfrisch\Edifact\Segment\GenericSegment;
use Apfelfrisch\Edifact\StreamMessageFactory;
use Apfelfrisch\Edifact\Test\Fixtures\ValidationSegment;
use Apfelfrisch\Edifact\Test\TestCase;
use Apfelfrisch\Edifact\Validation\Failure;
use Apfelfrisch\Edifact\Validation\Validator;
use Override;

final class ValidatorTest extends TestCase
{
    #[Override]
    protected function setUp(): void
    {
        ValidationSegment::$ruleOne = null;
        ValidationSegment::$ruleTwo = null;
    }

    public function test_throwing_exception_when_get_failures_was_called_before_is_valid(): void
    {
        $validator = new Validator();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(
            'No Message was validated, call [Apfelfrisch\Edifact\Validation\Validator::isValid] first',
        );
        $validator->getFailures();
    }

    public function test_throw_exception_when_validating_a_segment_wich_not_implements_the_validateable_interface(): void
    {
        $validator = new Validator();

        $segmentFactory = new StreamMessageFactory();
        $segmentFactory->addFallback(GenericSegment::class);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('[Apfelfrisch\Edifact\Segment\GenericSegment] not validateable');
        $validator->isValid($this->buildMessage('UNH+1', $segmentFactory));
    }

    public function test_validate_unknown_elements(): void
    {
        ValidationSegment::$ruleOne = 'M|n|1';

        $validator = new Validator();

        static::assertFalse($validator->isValid($this->buildMessage("UNH+1+unknown-element:unknown-component'")));

        static::assertInstanceOf(Failure::class, $failure = $validator->getFirstFailure());
        static::assertSame(2, $failure->elementPosition);
        static::assertSame('unknown-element', $failure->value);
        static::assertSame('The input Element is unknown', $failure->message);
    }

    public function test_validate_unknown_components(): void
    {
        ValidationSegment::$ruleOne = 'M|n|1';

        $validator = new Validator();

        static::assertFalse($validator->isValid($this->buildMessage("UNH+1::unknown-component'")));

        static::assertInstanceOf(Failure::class, $failure = $validator->getFirstFailure());

        static::assertSame(1, $failure->elementPosition);
        static::assertSame(2, $failure->componentPosition);
        static::assertSame('unknown-component', $failure->value);
        static::assertSame('The input Component is unknown', $failure->message);
    }

    public function test_validate_digit_values(): void
    {
        ValidationSegment::$ruleOne = 'M|n|..11';
        $digits = '01234567.1';

        $validator = new Validator();

        static::assertTrue($validator->isValid($this->buildMessage("UNH+{$digits}")));
        static::assertFalse($validator->isValid($this->buildMessage("UNH+{$digits}A")));
        static::assertInstanceOf(Failure::class, $failure = $validator->getFirstFailure());

        static::assertSame('UNH', $failure->segmentName);
        static::assertSame(1, $failure->elementPosition);
        static::assertSame(0, $failure->componentPosition);
        static::assertSame($digits . 'A', $failure->value);
        static::assertSame('String must contain only digits', $failure->message);
        static::assertSame(1, $failure->messageCounter);
        static::assertSame(1, $failure->unhCounter);
    }

    public function test_validate_alpha_values(): void
    {
        ValidationSegment::$ruleOne = 'M|a|..53';
        $alphaValues = implode(array_merge(range('a', 'z'), range('A', 'Z')));

        $validator = new Validator();

        static::assertTrue($validator->isValid($this->buildMessage("UNH+{$alphaValues}")));
        static::assertFalse($validator->isValid($this->buildMessage("UNH+{$alphaValues}1")));
        static::assertInstanceOf(Failure::class, $failure = $validator->getFirstFailure());
        static::assertSame($alphaValues . '1', $failure->value);
        static::assertSame('String must contain only alphabetic characters', $failure->message);
    }

    public function test_validate_max_lenght(): void
    {
        ValidationSegment::$ruleOne = 'M|an|..2';

        $validator = new Validator();

        static::assertTrue($validator->isValid($this->buildMessage("UNH+A'")));

        static::assertFalse($validator->isValid($this->buildMessage("UNH+ABC'")));
        static::assertInstanceOf(Failure::class, $failure = $validator->getFirstFailure());
        static::assertSame('ABC', $failure->value);
        static::assertSame('String is more than 2 characters long', $failure->message);
    }

    public function test_validate_needed_component(): void
    {
        ValidationSegment::$ruleOne = 'M|an|..2';

        $validator = new Validator();

        static::assertTrue($validator->isValid($this->buildMessage("UNH+A'")));

        static::assertFalse($validator->isValid($this->buildMessage('UNH+')));
        static::assertInstanceOf(Failure::class, $failure = $validator->getFirstFailure());
        static::assertSame('', $failure->value);
        static::assertSame('Component with Id [1:1] is missing', $failure->message);
    }

    public function test_validate_exact_string_lenght(): void
    {
        ValidationSegment::$ruleOne = 'M|an|3';

        $string = '123';

        $validator = new Validator();

        static::assertTrue($validator->isValid($this->buildMessage("UNH+123'")));

        static::assertFalse($validator->isValid($this->buildMessage("UNH+12'")));
        static::assertInstanceOf(Failure::class, $failure = $validator->getFirstFailure());
        static::assertSame('12', $failure->value);
        static::assertSame('String is not 3 characters long', $failure->message);

        static::assertFalse($validator->isValid($this->buildMessage("UNH+{$string}4")));
        static::assertInstanceOf(Failure::class, $failure = $validator->getFirstFailure());
        static::assertSame($string . '4', $failure->value);
        static::assertSame('String is not 3 characters long', $failure->message);
    }

    public function test_validate_missing_optional_element(): void
    {
        $validator = new Validator();

        ValidationSegment::$ruleOne = 'O|an|3';
        $segmentFactory = new StreamMessageFactory();
        $segmentFactory->addSegment('UNH', ValidationSegment::class);

        static::assertTrue($validator->isValid($this->buildMessage('UNH', $segmentFactory)));
    }

    public function test_validate_missing_required_element(): void
    {
        $validator = new Validator();

        ValidationSegment::$ruleOne = 'M|an|3';
        $segmentFactory = new StreamMessageFactory();
        $segmentFactory->addSegment('UNC', ValidationSegment::class);

        $message = $this->buildMessage("UNC'", $segmentFactory);

        static::assertFalse($validator->isValid($message));
        static::assertInstanceOf(Failure::class, $failure = $validator->getFirstFailure());
        static::assertSame(0, $failure->unhCounter);
        static::assertSame(0, $failure->messageCounter);
        static::assertSame(Failure::MISSING_COMPONENT, $failure->type);
    }

    public function test_validate_missing_optional_component(): void
    {
        $validator = new Validator();

        ValidationSegment::$ruleOne = 'M|an|3';
        ValidationSegment::$ruleTwo = 'O|an|3';

        $segmentFactory = new StreamMessageFactory();
        $segmentFactory->addSegment('UNH', ValidationSegment::class);

        static::assertTrue($validator->isValid($this->buildMessage("UNH+ABC'", $segmentFactory)));
    }

    public function test_skip_empty_rules(): void
    {
        $validator = new Validator();
        $segmentFactory = new StreamMessageFactory();
        $segmentFactory->addSegment('UNH', ValidationSegment::class);

        // Case One: Wrong Elements
        ValidationSegment::$ruleOne = null;
        ValidationSegment::$ruleTwo = 'M|an|3';

        static::assertTrue($validator->isValid($this->buildMessage("UNH+missing-rule:ABC'", $segmentFactory)));
    }

    public function test_validate_missing_required_component(): void
    {
        $validator = new Validator();

        ValidationSegment::$ruleOne = 'M|an|3';
        ValidationSegment::$ruleTwo = 'M|an|3';

        $segmentFactory = new StreamMessageFactory();
        $segmentFactory->addSegment('UNH', ValidationSegment::class);

        static::assertFalse($validator->isValid($this->buildMessage("UNH+ABC'", $segmentFactory)));
        static::assertInstanceOf(Failure::class, $failure = $validator->getFirstFailure());
        static::assertSame(Failure::MISSING_COMPONENT, $failure->type);
    }

    public function test_validate_missing_valid_dependend_component(): void
    {
        $validator = new Validator();

        ValidationSegment::$ruleOne = 'D|an|3';
        ValidationSegment::$ruleTwo = 'O|an|3';

        $message = $this->buildMessage('UNH+');

        static::assertTrue($validator->isValid($message));
        static::assertTrue($validator->isValid($message));
    }

    public function test_validate_missing_invalid_dependend_component(): void
    {
        $validator = new Validator();

        ValidationSegment::$ruleOne = 'D|an|3';
        ValidationSegment::$ruleTwo = 'O|an|3';

        $segmentFactory = new StreamMessageFactory();
        $segmentFactory->addSegment('UNH', ValidationSegment::class);

        $message = $this->buildMessage('UNH+:ABC');

        static::assertFalse($validator->isValid($message));
        static::assertInstanceOf(Failure::class, $failure = $validator->getFirstFailure());
        static::assertSame(Failure::MISSING_COMPONENT, $failure->type);
    }

    public function test_ignore_optional_empty_components(): void
    {
        ValidationSegment::$ruleOne = 'O|an|3';

        $validator = new Validator();

        static::assertTrue($validator->isValid($this->buildMessage('UNH')));
        static::assertFalse($validator->isValid($this->buildMessage("UNH+1234'")));
    }

    public function test_iterate_over_failures(): void
    {
        ValidationSegment::$ruleOne = 'M|n|1';

        $validator = new Validator();

        static::assertFalse($validator->isValid($this->buildMessage("UNH+AB'")));

        $i = 0;

        foreach ($validator->getFailures() as $failure) {
            $i++;
            static::assertInstanceOf(Failure::class, $failure);
        }

        static::assertSame(2, $i);
    }

    private function buildMessage(string $string, ?StreamMessageFactory $segmentFactory = null): Message
    {
        if ($segmentFactory === null) {
            $segmentFactory = new StreamMessageFactory();
            $segmentFactory->addSegment('UNH', ValidationSegment::class);
        }

        return $segmentFactory->fromString($string);
    }

    public function test_get_first_failure_returns_null_when_the_message_is_valid(): void
    {
        ValidationSegment::$ruleOne = 'O|an|3';

        $validator = new Validator();

        static::assertTrue($validator->isValid($this->buildMessage('UNH')));
        static::assertNull($validator->getFirstFailure());
    }
}
