<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact;

final class Elements
{
    private ?string $name = null;

    /** @psalm-var array<string, array<string, string|null>> */
    private array $elements = [];

    public function addValue(string $elementKey, string $componentKey, ?string $value): self
    {
        $this->elements[$elementKey][$componentKey] = $value;

        return $this;
    }

    public function getName(): string
    {
        return $this->name ??= $this->getValueFromPosition(0, 0) ?? '';
    }

    public function getValueFromPosition(int $elementPosition, int $valuePosition): ?string
    {
        return array_values(array_values($this->elements)[$elementPosition])[$valuePosition] ?? null;
    }

    public function getValue(string $elementKey, string $componentKey): ?string
    {
        return $this->elements[$elementKey][$componentKey] ?? null;
    }

    /**
     * @psalm-return array<string, string|null>
     */
    public function getElement(string $elementKey): array
    {
        return $this->elements[$elementKey] ?? [];
    }

    /**
     * @psalm-return array<string, array<string, string|null>>
     */
    public function toArray(): array
    {
        return $this->elements;
    }
}
