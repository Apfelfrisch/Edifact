<?php

namespace Proengeno\Edifact;

final class DataGroups
{
    /** @psalm-var array<string, array<string, string|null>> */
    private array $dataGroups = [];

    public function addValue(string $dataGroupKey, string $valueKey, ?string $value): self
    {
        $this->dataGroups[$dataGroupKey][$valueKey] = $value;

        return $this;
    }

    public function getValueFromPosition(int $dataGroupPosition, int $valuePosition): ?string
    {
        return array_values(array_values($this->dataGroups)[$dataGroupPosition])[$valuePosition] ?? null;
    }

    public function getValue(string $dataGroupKey, string $valueKey): ?string
    {
        return $this->dataGroups[$dataGroupKey][$valueKey] ?? null;
    }

    /**
     * @psalm-return array<string, string|null>
     */
    public function getDataGroup(string $dataGroupKey): array
    {
        return $this->dataGroups[$dataGroupKey] ?? [];
    }

    /**
     * @psalm-return array<string, array<string, string|null>>
     */
    public function toArray(): array
    {
        return $this->dataGroups;
    }
}
