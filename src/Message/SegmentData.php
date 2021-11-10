<?php

namespace Proengeno\Edifact\Message;

final class SegmentData
{
    private Delimiter $delimiter;
    private DataGroups $dataGroups;

    public function __construct(DataGroups $dataGroups, Delimiter $delimiter = null)
    {
        $this->dataGroups = $dataGroups;
        $this->delimiter = $delimiter ?? new Delimiter;
    }

    public function getDelimiter(): Delimiter
    {
        return $this->delimiter;
    }

    public function getValueFromPosition(int $dataGroupPosition, int $valuePosition): ?string
    {
        return $this->dataGroups->getValueFromPosition($dataGroupPosition, $valuePosition);
    }

    public function getValue(string $dataGroupKey, string $valueKey): ?string
    {
        return $this->dataGroups->getValue($dataGroupKey, $valueKey);
    }

    public function getNumericValue(string $dataGroupKey, string $valueKey): ?string
    {
        if (null === $value = $this->getValue($dataGroupKey, $valueKey)) {
            return $value;
        }

        return str_replace($this->delimiter->getDecimal(), '.', $value);
    }

    public function toString(): string
    {
        $string = '';

        foreach($this->dataGroups->toArray() as $dataGroup) {
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
