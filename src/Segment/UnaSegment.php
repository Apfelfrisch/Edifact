<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Segment;

use Apfelfrisch\Edifact\Exceptions\InvalidEdifactContentException;
use Apfelfrisch\Edifact\Stream\Stream;

final class UnaSegment
{
    public const UNA = 'UNA';

    public const PHP_SPACE = ' ';

    public const PHP_DECIMAL = '.';

    private static ?self $defaultUnaSegment = null;

    public function __construct(
        private string $componentSeparator = ':',
        private string $elementSeparator = '+',
        private string $decimalPoint = '.',
        private string $escapeCharacter = '?',
        private string $spaceCharacter = ' ',
        private string $segmentTerminator = '\''
    ) {
    }

    public static function setFromStream(Stream $stream, ?self $fallback = null): self
    {
        $position = $stream->tell();
        $stream->rewind();

        $instance = self::setFromString($stream->read(9), $fallback);

        $stream->seek($position);

        return $instance;
    }

    public static function setFromString(string $string, ?self $fallback = null): self
    {
        if (substr($string, 0, 3) !== self::UNA) {
            return $fallback ?? new self;
        }

        if (! isset($string[8])) {
            throw InvalidEdifactContentException::invalidUnaSegment($string);
        }

        return new self(
            $string[3],
            $string[4],
            $string[5],
            $string[6],
            $string[7],
            $string[8]
        );
    }

    public static function getDefault(): self
    {
        if (self::$defaultUnaSegment === null) {
            self::$defaultUnaSegment = new self();
        }

        return self::$defaultUnaSegment;
    }

    public function usesPhpDecimalPoint(): bool
    {
        return $this->decimalPoint() === self::PHP_DECIMAL;
    }

    public function usesPhpSpaceCharacter(): bool
    {
        return $this->spaceCharacter() === self::PHP_SPACE;
    }

    public function componentSeparator(): string
    {
        return $this->componentSeparator;
    }

    public function elementSeparator(): string
    {
        return $this->elementSeparator;
    }

    public function decimalPoint(): string
    {
        return $this->decimalPoint;
    }

    public function escapeCharacter(): string
    {
        return $this->escapeCharacter;
    }

    public function spaceCharacter(): string
    {
        return $this->spaceCharacter;
    }

    public function segmentTerminator(): string
    {
        return $this->segmentTerminator;
    }

    public function toString(): string
    {
        return self::UNA
            . $this->componentSeparator()
            . $this->elementSeparator()
            . $this->decimalPoint()
            . $this->escapeCharacter()
            . $this->spaceCharacter()
            . $this->segmentTerminator();
    }
}
