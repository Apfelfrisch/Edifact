<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Test\Segment;

use Apfelfrisch\Edifact\Exceptions\InvalidEdifactContentException;
use Apfelfrisch\Edifact\Iterators\Stream\Stream;
use Apfelfrisch\Edifact\Segment\UnaSegment;
use Apfelfrisch\Edifact\Test\TestCase;
use InvalidArgumentException;

final class UnaSegmentTest extends TestCase
{
    public function test_default_characters(): void
    {
        $unaSegment = new UnaSegment();

        static::assertSame(':', $unaSegment->componentSeparator());
        static::assertSame('+', $unaSegment->elementSeparator());
        static::assertSame('.', $unaSegment->decimalPoint());
        static::assertSame('?', $unaSegment->escapeCharacter());
        static::assertSame(' ', $unaSegment->spaceCharacter());
        static::assertSame("'", $unaSegment->segmentTerminator());
        static::assertSame("UNA:+.? '", $unaSegment->toString());
    }

    public function test_custom_characters(): void
    {
        $unaSegment = new UnaSegment('|', '#', ',', '!', '_', '"');

        static::assertSame('UNA|#,!_"', $unaSegment->toString());
    }

    public function test_empty_characters_are_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        // @mago-expect analysis:invalid-argument — deliberately violates the non-empty-string contract
        new UnaSegment(componentSeparator: '');
    }

    public function test_php_decimal_point_and_space_character_detection(): void
    {
        static::assertTrue(new UnaSegment()->usesPhpDecimalPoint());
        static::assertTrue(new UnaSegment()->usesPhpSpaceCharacter());

        $unaSegment = new UnaSegment(decimalPoint: ',', spaceCharacter: '_');

        static::assertFalse($unaSegment->usesPhpDecimalPoint());
        static::assertFalse($unaSegment->usesPhpSpaceCharacter());
    }

    public function test_set_from_string(): void
    {
        $unaSegment = UnaSegment::setFromString("UNA|#,!_\"SEG#1'");

        static::assertSame('UNA|#,!_"', $unaSegment->toString());
    }

    public function test_set_from_string_without_una_returns_the_fallback(): void
    {
        $fallback = new UnaSegment('|', '#');

        static::assertSame($fallback, UnaSegment::setFromString("SEG+1'", $fallback));
        static::assertEquals(new UnaSegment(), UnaSegment::setFromString("SEG+1'"));
    }

    public function test_set_from_string_with_incomplete_una_throws(): void
    {
        $this->expectException(InvalidEdifactContentException::class);
        UnaSegment::setFromString('UNA:+.?');
    }

    public function test_set_from_stream_restores_the_stream_position(): void
    {
        $stream = Stream::fromString('UNA|#,!_"SEG#1"');
        $stream->seek(4);

        $unaSegment = UnaSegment::setFromStream($stream);

        static::assertSame('UNA|#,!_"', $unaSegment->toString());
        static::assertSame(4, $stream->tell());
    }

    public function test_set_from_empty_stream_uses_the_fallback(): void
    {
        $stream = new Stream('php://temp', 'w+');
        $fallback = new UnaSegment('|', '#');

        static::assertSame($fallback, UnaSegment::setFromStream($stream, $fallback));
    }

    public function test_get_default_returns_the_same_instance(): void
    {
        static::assertSame(UnaSegment::getDefault(), UnaSegment::getDefault());
        static::assertSame("UNA:+.? '", UnaSegment::getDefault()->toString());
    }
}
