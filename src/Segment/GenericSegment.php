<?php

namespace Apfelfrisch\Edifact\Segment;

use Apfelfrisch\Edifact\Segment\SegmentInterface;
use Apfelfrisch\Edifact\Formatter\EdifactFormatter;

class GenericSegment implements SegmentInterface
{
    private Elements $elements;

    private ?UnaSegment $unaSegment = null;

    final protected function __construct(Elements $elements)
    {
        $this->elements = $elements;
    }

    /**
     * @psalm-param list<list<string>> $valueArrays
     */
    public static function fromAttributes(string $name, array ...$valueArrays): self
    {
        $elements = new Elements;
        $elements->addValue('0', '0', $name);
        $i = 1;
        foreach ($valueArrays as $values) {
            $j = 1;
            foreach($values as $value) {
                $elements->addValue((string)$i, (string)$j, $value);
                $j++;
            }
            $i++;
        }

        return new self($elements);
    }

    public static function fromSegLine(SeglineParser $parser, string $segLine): static
    {
        $segment = new static($parser->parse($segLine));
        $segment->setUnaSegment($parser->getUnaSegment());

        return $segment;
    }

    public function setUnaSegment(UnaSegment $unaSegment): void
    {
        $this->unaSegment = $unaSegment;
    }

    public function getValueFromPosition(int $elementPosition, int $valuePosition): ?string
    {
        return $this->elements->getValueFromPosition($elementPosition, $valuePosition);
    }

    public function getValue(string $elementKey, string $componentKey): ?string
    {
        return $this->elements->getValue($elementKey, $componentKey);
    }

    public function isValuNumeric(string $elementKey, string $componentKey): bool
    {
        return false;
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
