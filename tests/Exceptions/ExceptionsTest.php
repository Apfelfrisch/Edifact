<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Test\Exceptions;

use Apfelfrisch\Edifact\Exceptions\BuildException;
use Apfelfrisch\Edifact\Exceptions\EdifactException;
use Apfelfrisch\Edifact\Exceptions\InvalidEdifactContentException;
use Apfelfrisch\Edifact\Exceptions\InvalidStreamException;
use Apfelfrisch\Edifact\Exceptions\ValidationException;
use Apfelfrisch\Edifact\Test\TestCase;
use RuntimeException;

final class ExceptionsTest extends TestCase
{
    public function test_all_exceptions_extend_the_edifact_base_exception(): void
    {
        static::assertInstanceOf(RuntimeException::class, new EdifactException());
        static::assertInstanceOf(EdifactException::class, BuildException::finalizedBuildNotModifiable());
        static::assertInstanceOf(EdifactException::class, InvalidEdifactContentException::noSegmentAvailable());
        static::assertInstanceOf(EdifactException::class, InvalidStreamException::readError('file'));
        static::assertInstanceOf(EdifactException::class, ValidationException::messageNotValidated());
    }

    public function test_build_exception_messages(): void
    {
        static::assertSame(
            'Cannot add Segments to finalized Builder.',
            BuildException::finalizedBuildNotModifiable()->getMessage(),
        );
    }

    public function test_invalid_edifact_content_exception_messages(): void
    {
        static::assertSame(
            "Segment not registered: ['SEQ']",
            InvalidEdifactContentException::segmentUnknown('SEQ')->getMessage(),
        );
        static::assertSame(
            'Unknown message key [foo]',
            InvalidEdifactContentException::messageUnknown('foo')->getMessage(),
        );
        static::assertSame(
            "Invalid Una Segment: ['UNA:+']",
            InvalidEdifactContentException::invalidUnaSegment('UNA:+')->getMessage(),
        );
        static::assertSame('No Segment available.', InvalidEdifactContentException::noSegmentAvailable()->getMessage());
    }

    public function test_invalid_stream_exception_messages(): void
    {
        static::assertSame(
            'Failed to open stream: [path/to/file]',
            InvalidStreamException::readError('path/to/file')->getMessage(),
        );
        static::assertSame(
            "Unable to locate filter: ['string.missing']",
            InvalidStreamException::filterError('string.missing')->getMessage(),
        );
    }

    public function test_validation_exception_messages(): void
    {
        static::assertSame(
            '[Some\Segment] not validateable.',
            ValidationException::segmentNotValidateable('Some\Segment')->getMessage(),
        );
        static::assertSame(
            'No Message was validated, call [Apfelfrisch\Edifact\Validation\Validator::isValid] first.',
            ValidationException::messageNotValidated()->getMessage(),
        );
        static::assertSame('Invalid Validation Rule [M|x].', ValidationException::invalidRule('M|x')->getMessage());
    }
}
