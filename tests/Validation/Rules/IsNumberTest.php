<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Test\Validation\Rules;

use Apfelfrisch\Edifact\Test\TestCase;
use Apfelfrisch\Edifact\Validation\Rules\IsNumber;

final class IsNumberTest extends TestCase
{
    public function test_numeric_strings_are_valid(): void
    {
        static::assertTrue((new IsNumber())('0'));
        static::assertTrue((new IsNumber())('123'));
        static::assertTrue((new IsNumber())('1.5'));
        static::assertTrue((new IsNumber())('-2'));
        static::assertTrue((new IsNumber())('1e3'));
    }

    public function test_non_numeric_strings_are_invalid(): void
    {
        static::assertFalse((new IsNumber())(''));
        static::assertFalse((new IsNumber())('abc'));
        static::assertFalse((new IsNumber())('1,5'));
        static::assertFalse((new IsNumber())('1.2.3'));
        static::assertFalse((new IsNumber())('12a'));
    }
}
