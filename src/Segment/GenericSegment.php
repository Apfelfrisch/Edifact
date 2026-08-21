<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Segment;

final class GenericSegment implements Segment
{
    private function __construct(
        private readonly Elements $elements,
    ) {}

    /**
     * @param list<string|null> ...$valueArrays
     */
    public static function fromAttributes(string $name, array ...$valueArrays): self
    {
        $elements = new Elements();
        $elements->addValue('0', '0', $name);

        $elementPosition = 1;

        foreach ($valueArrays as $values) {
            $componentPosition = 1;

            foreach ($values as $value) {
                $elements->addValue((string) $elementPosition, (string) $componentPosition, $value);
                $componentPosition++;
            }

            $elementPosition++;
        }

        return new self($elements);
    }

    public static function fromSegLine(SeglineParser $parser, string $segLine): static
    {
        return new self($parser->parse($segLine));
    }

    public function getValueFromPosition(int $elementPosition, int $valuePosition): null|string
    {
        return $this->elements->getValueFromPosition($elementPosition, $valuePosition);
    }

    public function getValue(string $elementKey, string $componentKey): null|string
    {
        return $this->elements->getValue($elementKey, $componentKey);
    }

    public function isValueNumeric(string $elementKey, string $componentKey): bool
    {
        return false;
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
