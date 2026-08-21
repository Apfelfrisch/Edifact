<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Test\Validation;

use Apfelfrisch\Edifact\Exceptions\ValidationException;
use Apfelfrisch\Edifact\Test\TestCase;
use Apfelfrisch\Edifact\Validation\ValueValidator;

final class ValueValidatorTest extends TestCase
{
    public function test_optional_value(): void
    {
        $validator = new ValueValidator();

        static::assertEmpty($validator->validate(null, 'O|a|1', ''));
        static::assertEmpty($validator->validate('a', 'O|a|1', ''));
    }

    public function test_needed_value(): void
    {
        $validator = new ValueValidator();

        static::assertCount(1, $validator->validate(null, 'M|a|1', ''));
        static::assertCount(1, $validator->validate('', 'M|a|1', ''));
    }

    public function test_numeric_value(): void
    {
        $validator = new ValueValidator();

        static::assertCount(1, $validator->validate('a', 'M|n|1', ''));
        static::assertEmpty($validator->validate('1', 'M|n|1', ''));
    }

    public function test_alpha_value(): void
    {
        $validator = new ValueValidator();

        static::assertCount(1, $validator->validate('1', 'M|a|1', ''));
        static::assertEmpty($validator->validate('a', 'M|a|1', ''));
    }

    public function test_exact_value_length(): void
    {
        $validator = new ValueValidator();

        static::assertCount(1, $validator->validate('abcd', 'M|a|3', ''));
        static::assertCount(1, $validator->validate('ab', 'M|a|3', ''));
        static::assertEmpty($validator->validate('abc', 'M|a|3', ''));
        static::assertEmpty($validator->validate('abc', 'M|a|3', ''));
        static::assertEmpty($validator->validate('🙂🙂🙂', 'M|an|3', ''));
    }

    public function test_maximum_value_length(): void
    {
        $validator = new ValueValidator();

        static::assertCount(1, $validator->validate('', 'M|a|..3', ''));
        static::assertCount(1, $validator->validate('abcd', 'M|a|..3', ''));
        static::assertEmpty($validator->validate('ab', 'M|a|..3', ''));
    }

    public function test_multiple_failures(): void
    {
        $validator = new ValueValidator();

        static::assertCount(2, $validator->validate('ac', 'M|n|3', ''));
        static::assertCount(2, $validator->validate('acde', 'M|n|..3', ''));
    }

    public function test_alpha_numeric_values_accept_letters_and_digits(): void
    {
        $validator = new ValueValidator();

        static::assertEmpty($validator->validate('a1', 'M|an|2', ''));
        static::assertEmpty($validator->validate('1a', 'M|an|..5', ''));
    }

    public function test_throws_on_invalid_rules(): void
    {
        $this->expectException(ValidationException::class);
        new ValueValidator()->validate('a', 'M|an', '');
    }

    public function test_dependent_component_is_ignored_when_the_element_stays_empty(): void
    {
        $validator = new ValueValidator();

        static::assertEmpty($validator->validate('', 'D|an|3', '1'));
        static::assertEmpty($validator->validate('abc', 'O|an|3', '2'));
    }

    public function test_dependent_component_fails_when_the_element_is_set(): void
    {
        $validator = new ValueValidator();

        static::assertEmpty($validator->validate('', 'D|an|3', '1'));
        static::assertCount(1, $validator->validate('abc', 'O|an|3', '1'));
    }
}
