<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Segment;

use Apfelfrisch\Edifact\Exceptions\InvalidEdifactContentException;
use Apfelfrisch\Edifact\Iterators\Stream\Stream;
use InvalidArgumentException;

final class UnaSegment
{
    public const string UNA = 'UNA';

    public const string PHP_SPACE = ' ';

    public const string PHP_DECIMAL = '.';

    private static ?self $defaultUnaSegment = null;

    /**
     * @param non-empty-string $componentSeparator
     * @param non-empty-string $elementSeparator
     * @param non-empty-string $decimalPoint
     * @param non-empty-string $escapeCharacter
     * @param non-empty-string $spaceCharacter
     * @param non-empty-string $segmentTerminator
     */
    public function __construct(
        private readonly string $componentSeparator = ':',
        private readonly string $elementSeparator = '+',
        private readonly string $decimalPoint = '.',
        private readonly string $escapeCharacter = '?',
        private readonly string $spaceCharacter = ' ',
        private readonly string $segmentTerminator = "'",
    ) {
        foreach ([
            $componentSeparator,
            $elementSeparator,
            $decimalPoint,
            $escapeCharacter,
            $spaceCharacter,
            $segmentTerminator,
        ] as $character) {
            // Defense in depth: enforce the non-empty-string contract at runtime
            // @mago-expect analysis:impossible-condition
            // @mago-expect analysis:redundant-comparison
            if ($character === '') {
                throw new InvalidArgumentException('UNA characters must be non-empty strings.');
            }
        }
    }

    public static function setFromStream(Stream $stream, ?self $fallback = null): self
    {
        $position = $stream->tell();
        $stream->rewind();

        $unaString = $stream->read(9);

        $instance = self::setFromString($unaString === false ? '' : $unaString, $fallback);

        $stream->seek($position);

        return $instance;
    }

    public static function setFromString(string $string, ?self $fallback = null): self
    {
        if (!str_starts_with($string, self::UNA)) {
            return $fallback ?? new self();
        }

        if (strlen($string) < 9) {
            throw InvalidEdifactContentException::invalidUnaSegment($string);
        }

        return new self(
            self::characterAt($string, 3),
            self::characterAt($string, 4),
            self::characterAt($string, 5),
            self::characterAt($string, 6),
            self::characterAt($string, 7),
            self::characterAt($string, 8),
        );
    }

    /** @return non-empty-string */
    private static function characterAt(string $string, int $offset): string
    {
        $character = substr($string, $offset, 1);

        if ($character === '') {
            throw InvalidEdifactContentException::invalidUnaSegment($string);
        }

        return $character;
    }

    public static function getDefault(): self
    {
        return self::$defaultUnaSegment ??= new self();
    }

    public function usesPhpDecimalPoint(): bool
    {
        return $this->decimalPoint === self::PHP_DECIMAL;
    }

    public function usesPhpSpaceCharacter(): bool
    {
        return $this->spaceCharacter === self::PHP_SPACE;
    }

    /** @return non-empty-string */
    public function componentSeparator(): string
    {
        return $this->componentSeparator;
    }

    /** @return non-empty-string */
    public function elementSeparator(): string
    {
        return $this->elementSeparator;
    }

    /** @return non-empty-string */
    public function decimalPoint(): string
    {
        return $this->decimalPoint;
    }

    /** @return non-empty-string */
    public function escapeCharacter(): string
    {
        return $this->escapeCharacter;
    }

    /** @return non-empty-string */
    public function spaceCharacter(): string
    {
        return $this->spaceCharacter;
    }

    /** @return non-empty-string */
    public function segmentTerminator(): string
    {
        return $this->segmentTerminator;
    }

    public function toString(): string
    {
        return (
            self::UNA
            . $this->componentSeparator
            . $this->elementSeparator
            . $this->decimalPoint
            . $this->escapeCharacter
            . $this->spaceCharacter
            . $this->segmentTerminator
        );
    }
}
