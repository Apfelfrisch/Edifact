<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Test\Validation;

use Apfelfrisch\Edifact\Test\TestCase;
use Apfelfrisch\Edifact\Validation\Failure;
use Apfelfrisch\Edifact\Validation\SyntaxErrorCode;

final class SyntaxErrorCodeTest extends TestCase
{
    public function test_the_codes_match_the_un_edifact_0085_code_list(): void
    {
        static::assertSame(12, SyntaxErrorCode::InvalidValue->value);
        static::assertSame(13, SyntaxErrorCode::Missing->value);
        static::assertSame(28, SyntaxErrorCode::ReferencesDoNotMatch->value);
        static::assertSame(29, SyntaxErrorCode::ControlCountDoesNotMatch->value);
        static::assertSame(39, SyntaxErrorCode::DataElementTooLong->value);
        static::assertSame(40, SyntaxErrorCode::DataElementTooShort->value);
    }

    public function test_every_code_has_a_description(): void
    {
        foreach (SyntaxErrorCode::cases() as $code) {
            static::assertNotSame('', $code->description());
        }
    }

    public function test_mapping_failure_types_to_syntax_error_codes(): void
    {
        static::assertSame(SyntaxErrorCode::Missing, SyntaxErrorCode::fromFailureType(Failure::MISSING_ELEMENT));
        static::assertSame(SyntaxErrorCode::Missing, SyntaxErrorCode::fromFailureType(Failure::MISSING_COMPONENT));
        static::assertSame(
            SyntaxErrorCode::TooManyConstituents,
            SyntaxErrorCode::fromFailureType(Failure::UNKNOWN_ELEMENT),
        );
        static::assertSame(
            SyntaxErrorCode::TooManyConstituents,
            SyntaxErrorCode::fromFailureType(Failure::UNKNOWN_COMPONENT),
        );
        static::assertSame(
            SyntaxErrorCode::InvalidCharacterType,
            SyntaxErrorCode::fromFailureType(Failure::VALUE_NOT_ALPHA),
        );
        static::assertSame(
            SyntaxErrorCode::InvalidCharacterType,
            SyntaxErrorCode::fromFailureType(Failure::VALUE_NOT_DIGIT),
        );
        static::assertSame(
            SyntaxErrorCode::DataElementTooLong,
            SyntaxErrorCode::fromFailureType(Failure::VALUE_TOO_LONG),
        );
        static::assertSame(
            SyntaxErrorCode::DataElementTooShort,
            SyntaxErrorCode::fromFailureType(Failure::VALUE_TOO_SHORT),
        );
        static::assertSame(
            SyntaxErrorCode::InvalidValue,
            SyntaxErrorCode::fromFailureType(Failure::VALUE_LENGTH_INVALID),
        );
    }
}
