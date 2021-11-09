<?php

namespace Proengeno\Edifact\Message;

final class DataGroupCollection
{
    private Delimiter $delimiter;

    /** @var array<string, array<string, string|null>> */
    private array $dataGroups = [];

    public function __construct(Delimiter $delimiter = null)
    {
        $this->delimiter = $delimiter ?? new Delimiter;
    }

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

    public function toString(): string
    {
        $string = '';

        foreach($this->dataGroups as $dataGroup) {
            foreach ($dataGroup as $value) {
                $string .= $value === null
                    ? $this->delimiter->getData()
                    : $this->delimiter->terminate($value) . $this->delimiter->getData();
            }

            $string = $this->trimEmpty(
                $string, $this->delimiter->getData()
            ) . $this->delimiter->getDataGroup();
        }

        return $this->trimEmpty($string, $this->delimiter->getDataGroup());
    }

    private function trimEmpty(string $string, string $delimiter): string
    {
        while(true) {
            if ($delimiter !== $string[-1] ?? null) {
                break;
            }

            if ($this->delimiter->getTerminator() === $string[-2] ?? null) {
                break;
            }

            $string = substr($string, 0, -1);
        }

        return $string;
    }
}
