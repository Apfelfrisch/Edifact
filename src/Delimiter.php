<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact;

final class Delimiter
{
    private static ?self $defaultDelimiter = null;

    const UNA_SEGMENT = 'UNA';

    public function __construct(
        private string $componentSeparator = ':',
        private string $elementSeparator = '+',
        private string $decimalPoint = '.',
        private string $escapeCharacter = '?',
        private string $spaceCharacter = ' ',
        private string $segmentTerminator = '\''
    ) { }

    public static function setFromFile(Stream $file, ?self $fallback = null): self
    {
        $position = $file->tell();
        $file->rewind();

        $instance = self::setFromString($file->read(9), $fallback);

        $file->seek($position);

        return $instance;
    }

    public static function setFromString(string $string, ?self $fallback = null): self
    {
        if (substr($string, 0, 3) !== self::UNA_SEGMENT) {
            return $fallback ?? new self();
        }

        if (! isset($string[8])) {
            return $fallback ?? new self();
        }

        return new self(
            $string[3], $string[4], $string[5], $string[6], $string[7], $string[8]
        );
    }

    public static function getDefault(): self
    {
        if (self::$defaultDelimiter === null) {
            self::$defaultDelimiter = new self();
        }

        return self::$defaultDelimiter;
    }

    public function getComponentSeparator(): string
    {
        return $this->componentSeparator;
    }

    public function getElementSeparator(): string
    {
        return $this->elementSeparator;
    }

    public function getDecimalPoint(): string
    {
        return $this->decimalPoint;
    }

    public function getEscapeCharacter(): string
    {
        return $this->escapeCharacter;
    }

    public function getSpaceCharacter(): string
    {
        return $this->spaceCharacter;
    }

    public function getSegmentTerminator(): string
    {
        return $this->segmentTerminator;
    }
}
