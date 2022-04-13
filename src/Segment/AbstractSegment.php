<?php

namespace Apfelfrisch\Edifact\Segment;

use Apfelfrisch\Edifact\Validation\SegmentValidator;
use Apfelfrisch\Edifact\Validation\ValidateableInterface;
use Iterator;

abstract class AbstractSegment implements SegmentInterface, ValidateableInterface
{
    protected Elements $elements;

    final protected function __construct(Elements $elements)
    {
        $this->elements = $elements;
    }

    abstract public static function blueprint(): Elements;

    public static function fromSegLine(SeglineParser $parser, string $segLine): static
    {
        return new static($parser->parseToBlueprint($segLine, static::blueprint()));
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

    public function getValue(string $elementKey, string $componentKey): ?string
    {
        return $this->elements->getValue($elementKey, $componentKey);
    }

    public function isValueNumeric(string $elementKey, string $componentKey): bool
    {
        $value = static::blueprint()->getValue($elementKey, $componentKey);

        return $value !== null && strpos($value, '|n|') !== false;
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
}
