<?php

namespace Apfelfrisch\Edifact\Segment;

use Apfelfrisch\Edifact\Segment\SegmentInterface;
use Apfelfrisch\Edifact\Validation\ValidateableInterface;
use Apfelfrisch\Edifact\Validation\SegmentValidator;
use Apfelfrisch\Edifact\Formatter\EdifactFormatter;
use Iterator;

abstract class AbstractSegment implements SegmentInterface, ValidateableInterface
{
    public const SPACE_CHARACTER = ' ';

    public const DECIMAL_POINT = '.';

    protected ?UnaSegment $unaSegment = null;

    protected Elements $elements;

    final protected function __construct(Elements $elements)
    {
        $this->elements = $elements;
    }

    abstract public static function blueprint(): Elements;

    public static function fromSegLine(SeglineParser $parser, string $segLine): static
    {
        $segment = new static($parser->parseToBlueprint($segLine, static::blueprint()));
        $segment->setUnaSegment($parser->getUnaSegment());

        return $segment;
    }

    public function setUnaSegment(UnaSegment $unaSegment): void
    {
        $this->unaSegment = $unaSegment;
    }

    /** @psalm-return Iterator<\Apfelfrisch\Edifact\Validation\Failure> */
    public function validate(SegmentValidator $segmentValidator): Iterator
    {
        return $segmentValidator->validate(static::blueprint(), $this->elements);
    }

    public function getValueFromPosition(int $elementPosition, int $valuePosition): ?string
    {
        return $this->elements->getValueFromPosition($elementPosition, $valuePosition);
    }

    public function getValue(string|int $elementKey, string|int $componentKey): ?string
    {
        if (
            $this->getUnaSegment()->usesPhpSpaceCharacter()
            && ($this->getUnaSegment()->usesPhpDecimalPoint() || ! $this->isValuNumeric($elementKey, $componentKey))
        ) {
            return $this->elements->getValue($elementKey, $componentKey);
        }

        if (null === $value = $this->elements->getValue($elementKey, $componentKey)) {
            return $value;
        }

        if (! $this->getUnaSegment()->usesPhpSpaceCharacter()) {
            $value = str_replace($this->getUnaSegment()->spaceCharacter(), ' ', $value);
        }

        if (! $this->getUnaSegment()->usesPhpDecimalPoint()) {
            $value = str_replace($this->getUnaSegment()->decimalPoint(), '.', $value);
        }

        return $value;
    }

    public function isValuNumeric(string|int $elementKey, string|int $componentKey): bool
    {
        $value = static::blueprint()->getValue($elementKey, $componentKey);

        return $value !== null && strpos($value, '|n|') !== false;
    }

    /** @deprected */
    public function replaceDecimalPoint(?string $value): ?string
    {
        if ($this->getUnaSegment()->decimalPoint() !== self::DECIMAL_POINT && $value !== null) {
            return str_replace($this->getUnaSegment()->decimalPoint(), self::DECIMAL_POINT, $value);
        }

        return $value;
    }

    /** @deprected */
    public function replaceSpaceCharacter(?string $value): ?string
    {
        if ($this->getUnaSegment()->spaceCharacter() !== self::SPACE_CHARACTER && $value !== null) {
            return str_replace($this->getUnaSegment()->spaceCharacter(), self::SPACE_CHARACTER, $value);
        }

        return $value;
    }

    public function name(): string
    {
        return $this->elements->getName();
    }

    /**
     * @psalm-return array<string, array<string, string|null>>
     */
    public function toArray(): array
    {
        return $this->elements->toArray();
    }

    public function toString(): string
    {
        return substr((new EdifactFormatter($this->getUnaSegment()))->format($this), 0, -1);
    }

    private function getUnaSegment(): UnaSegment
    {
        return $this->unaSegment ??= UnaSegment::getDefault();
    }
}
