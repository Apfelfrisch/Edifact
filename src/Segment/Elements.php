<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Segment;

final class Elements
{
    private null|string $name = null;

    /** @var array<array-key, array<array-key, string|null>> */
    private array $elements = [];

    public function addValue(string $elementKey, string $componentKey, null|string $value): self
    {
        $this->elements[$elementKey][$componentKey] = $value;

        return $this;
    }

    public function getName(): string
    {
        return $this->name ??= $this->getValueFromPosition(0, 0) ?? '';
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
