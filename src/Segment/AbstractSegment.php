<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Segment;

use Apfelfrisch\Edifact\Validation\Failure;
use Apfelfrisch\Edifact\Validation\SegmentValidator;
use Apfelfrisch\Edifact\Validation\Validateable;
use Iterator;

abstract class AbstractSegment implements Segment, Validateable
{
    final protected function __construct(
        protected readonly Elements $elements,
    ) {}

    abstract public static function blueprint(): Elements;

    public static function fromSegLine(SeglineParser $parser, string $segLine): static
    {
        return new static($parser->parseToBlueprint($segLine, static::blueprint()));
    }

    /** @return Iterator<Failure> */
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

        return $value !== null && str_contains($value, '|n|');
    }

    public function name(): string
    {
        return $this->elements->getName();
    }

    /**
     * @return array<array-key, array<array-key, string|null>>
     */
    public function toArray(): array
    {
        return $this->elements->toArray();
    }
}
