<?php

namespace Proengeno\Edifact\Test\Validation;

use Proengeno\Edifact\Test\TestCase;
use Proengeno\Edifact\Validation\MessageValidator;
use Proengeno\Edifact\Validation\SegmentValidator;
use Proengeno\Edifact\Exceptions\SegValidationException;

class SegmentValidatorTest extends TestCase
{
    private $validator;

    public function setUp(): void
    {
        $this->validator = new SegmentValidator;
    }

    /** @test */
    public function it_ignores_can_handle_null_value_elements()
    {
        $blueprint = ['7077' => ['7077' => null]];
        $data = ['7077' => ['7077' => null]];

        $this->assertInstanceOf(get_class($this->validator), $this->validator->validate($blueprint, $data));
    }

    /** @test */
    public function it_ignores_validation_for_optional_elements()
    {
        $blueprint = ['A' => ['1' => 'M|n|1', '2' => 'O|n|1', '3' => 'M|an|1']];
        $data = ['A' => ['1' => '1', '3' => 'A']];

        $this->assertInstanceOf(get_class($this->validator), $this->validator->validate($blueprint, $data));
    }

    /** @test */
    public function it_checks_if_the_string_is_alpha()
    {
        $blueprint = ['A' => ['A' => 'M|a|6']];
        $data = ['A' => ['A' => 'APLHA ']];

        $this->assertInstanceOf(get_class($this->validator), $this->validator->validate($blueprint, $data));
    }

    /** @test */
    public function it_warns_if_the_string_is_not_alpha()
    {
        $blueprint = ['A' => ['A' => 'M|a|1']];
        $data = ['A' => ['A' => '1']];

        $this->expectException(SegValidationException::class);
        $this->expectExceptionCode(3);
        $this->validator->validate($blueprint, $data);
    }

    /** @test */
    public function it_checks_if_the_string_is_numeric()
    {
        $blueprint = ['A' => ['A' => 'M|n|1']];
        $data = ['A' => ['A' => 'A']];

        $this->expectException(SegValidationException::class);
        $this->expectExceptionCode(2);
        $this->validator->validate($blueprint, $data);
    }

    /** @test */
    public function it_checks_if_the_data_element_is_smaller_then_the_maximum_length()
    {
        $blueprint = ['A' => ['A' => 'M|an|14']];
        $data = ['A' => ['A' => '15_chars_lenght']];

        $this->expectException(SegValidationException::class);
        $this->expectExceptionCode(5);
        $this->validator->validate($blueprint, $data);
    }

    /** @test */
    public function it_checks_if_a_requiered_data_element_is_empty()
    {
        $blueprint = ['A' => ['A' => 'M|an|14']];
        $data = ['A' => ['A' => '']];

        $this->expectException(SegValidationException::class);
        $this->expectExceptionCode(4);
        $this->validator->validate($blueprint, $data);
    }

    /** @test */
    public function it_checks_if_all_needed_data_element_are_available()
    {
        $blueprint = ['A' => ['A' => 'M|n|1']];
        $data = ['A' => ['B' => 'B']];

        $this->expectException(SegValidationException::class);
        $this->expectExceptionCode(1);
        $this->validator->validate($blueprint, $data);
    }

    /** @test */
    public function it_checks_if_illegall_data_element_where_given()
    {
        $blueprint = ['A' => ['2' => 'M|n|1']];
        $data = ['A' => ['UNKNOWN_ELEMENT' => '1', '2' => '1']];

        $this->expectException(SegValidationException::class);
        $this->expectExceptionCode(6);
        $this->validator->validate($blueprint, $data);
    }

    /** @test */
    public function it_checks_if_illegall_data_groups_where_given()
    {
        $blueprint = ['A' => ['1' => 'M|an|2']];
        $data = ['A' => ['1' => 'OK'], 'B' => ['1' => 'Not Okay']];

        $this->expectException(SegValidationException::class);
        $this->expectExceptionCode(7);
        $this->validator->validate($blueprint, $data);
    }
}
