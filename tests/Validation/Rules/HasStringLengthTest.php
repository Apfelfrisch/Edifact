<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Test\Validation\Rules;

use Apfelfrisch\Edifact\Test\TestCase;
use Apfelfrisch\Edifact\Validation\Rules\HasStringLength;

final class HasStringLengthTest extends TestCase
{
    public function test_without_bounds_every_string_is_valid(): void
    {
        static::assertTrue((new HasStringLength())(''));
        static::assertTrue((new HasStringLength())('abc'));
    }

    public function test_minimum_length(): void
    {
        $rule = new HasStringLength()->min(2);

        static::assertFalse($rule(''));
        static::assertFalse($rule('a'));
        static::assertTrue($rule('ab'));
        static::assertTrue($rule('abc'));
    }

    public function test_maximum_length(): void
    {
        $rule = new HasStringLength()->max(2);

        static::assertTrue($rule(''));
        static::assertTrue($rule('ab'));
        static::assertFalse($rule('abc'));
    }

    public function test_minimum_and_maximum_length(): void
    {
        $rule = new HasStringLength(min: 2, max: 3);

        static::assertFalse($rule('a'));
        static::assertTrue($rule('ab'));
        static::assertTrue($rule('abc'));
        static::assertFalse($rule('abcd'));
    }

    public function test_length_is_counted_in_characters_not_bytes(): void
    {
        $rule = new HasStringLength(min: 3, max: 3);

        static::assertTrue($rule('🙂🙂🙂'));
        static::assertTrue($rule('äöü'));
        static::assertFalse($rule('äöüß'));
    }
}
