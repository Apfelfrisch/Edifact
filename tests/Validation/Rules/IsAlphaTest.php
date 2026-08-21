<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Test\Validation\Rules;

use Apfelfrisch\Edifact\Test\TestCase;
use Apfelfrisch\Edifact\Validation\Rules\IsAlpha;

final class IsAlphaTest extends TestCase
{
    public function test_alphabetic_strings_are_valid(): void
    {
        static::assertTrue((new IsAlpha())('abc'));
        static::assertTrue((new IsAlpha())('ABC'));
        static::assertTrue((new IsAlpha())('aBc'));
    }

    public function test_non_alphabetic_strings_are_invalid(): void
    {
        static::assertFalse((new IsAlpha())(''));
        static::assertFalse((new IsAlpha())('abc1'));
        static::assertFalse((new IsAlpha())('a b'));
        static::assertFalse((new IsAlpha())('a-b'));
        static::assertFalse((new IsAlpha())('123'));
    }
}
