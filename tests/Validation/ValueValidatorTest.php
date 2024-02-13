<?php

namespace Apfelfrisch\Edifact\Test\Validation;

use Apfelfrisch\Edifact\Test\TestCase;
use Apfelfrisch\Edifact\Validation\ValueValidator;

class ValueValidatorTest extends TestCase
{
    /** @test */
    public function test_optional_value(): void
    {
        $validator = new ValueValidator();

        $this->assertEmpty($validator->validate(null, 'O|a|1', ''));
        $this->assertEmpty($validator->validate('a', 'O|a|1', ''));
    }

    /** @test */
    public function test_needed_value(): void
    {
        $validator = new ValueValidator();

        $this->assertCount(1, $validator->validate(null, 'M|a|1', ''));
        $this->assertCount(1, $validator->validate('', 'M|a|1', ''));
    }

    /** @test */
    public function test_numeric_value(): void
    {
        $validator = new ValueValidator();

        $this->assertCount(1, $validator->validate('a', 'M|n|1', ''));
        $this->assertEmpty($validator->validate('1', 'M|n|1', ''));
    }

    /** @test */
    public function test_alpha_value(): void
    {
        $validator = new ValueValidator();

        $this->assertCount(1, $validator->validate('1', 'M|a|1', ''));
        $this->assertEmpty($validator->validate('a', 'M|a|1', ''));
    }

    /** @test */
    public function test_exact_value_length(): void
    {
        $validator = new ValueValidator();

        $this->assertCount(1, $validator->validate('abcd', 'M|a|3', ''));
        $this->assertCount(1, $validator->validate('ab', 'M|a|3', ''));
        $this->assertEmpty($validator->validate('abc', 'M|a|3', ''));
        $this->assertEmpty($validator->validate('abc', 'M|a|3', ''));
        $this->assertEmpty($validator->validate('🙂🙂🙂', 'M|an|3', ''));
    }

    /** @test */
    public function test_maximum_value_length(): void
    {
        $validator = new ValueValidator();

        $this->assertCount(1, $validator->validate('', 'M|a|..3', ''));
        $this->assertCount(1, $validator->validate('abcd', 'M|a|..3', ''));
        $this->assertEmpty($validator->validate('ab', 'M|a|..3', ''));
    }

    /** @test */
    public function test_multiple_failures(): void
    {
        $validator = new ValueValidator();

        $this->assertCount(2, $validator->validate('ac', 'M|n|3', ''));
        $this->assertCount(2, $validator->validate('acde', 'M|n|..3', ''));
    }
}
