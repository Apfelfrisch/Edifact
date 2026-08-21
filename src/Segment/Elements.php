<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Segment;

final class Elements
{
    private null|string $name = null;

    /**
     * @param array<array-key, array<array-key, string|null>> $elements
     */
    public function __construct(
        private array $elements = [],
    ) {}

    public function addValue(string $elementKey, string $componentKey, null|string $value): self
    {
        $this->elements[$elementKey][$componentKey] = $value;

        return $this;
    }

    public function getName(): string
    {
        if ($this->name !== null) {
            return $this->name;
        }

        $firstElementKey = array_key_first($this->elements);

        if ($firstElementKey === null) {
            return $this->name = '';
        }

        $firstElement = $this->elements[$firstElementKey];
        $firstComponentKey = array_key_first($firstElement);

        return $this->name = $firstComponentKey === null ? '' : $firstElement[$firstComponentKey] ?? '';
    }

    public function getValueFromPosition(int $elementPosition, int $valuePosition): null|string
    {
        $components = array_values($this->elements)[$elementPosition] ?? [];

        return array_values($components)[$valuePosition] ?? null;
    }

    public function getValue(string $elementKey, string $componentKey): null|string
    {
        return $this->elements[$elementKey][$componentKey] ?? null;
    }

    /**
     * @return array<array-key, string|null>
     */
    public function getElement(string $elementKey): array
    {
        return $this->elements[$elementKey] ?? [];
    }

    /**
     * @return list<string>
     */
    public function getElementKeys(): array
    {
        return array_map(strval(...), array_keys($this->elements));
    }

    public function getElementKeyFromPosition(int $elementPosition): null|string
    {
        return $this->getElementKeys()[$elementPosition] ?? null;
    }

    /**
     * @return list<string>
     */
    public function getComponentKeys(string $elementKey): array
    {
        return array_map(strval(...), array_keys($this->getElement($elementKey)));
    }

    public function getComponentKeyFromPosition(string $elementKey, int $valuePosition): null|string
    {
        return $this->getComponentKeys($elementKey)[$valuePosition] ?? null;
    }

    /**
     * @return array<array-key, array<array-key, string|null>>
     */
    public function toArray(): array
    {
        return $this->elements;
    }
}
