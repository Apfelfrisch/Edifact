<?php

namespace Proengeno\Edifact;

final class SegmentData
{
    private DataGroups $dataGroups;

    public function __construct(DataGroups $dataGroups)
    {
        $this->dataGroups = $dataGroups;
    }

    public function getValueFromPosition(int $dataGroupPosition, int $valuePosition): ?string
    {
        return $this->dataGroups->getValueFromPosition($dataGroupPosition, $valuePosition);
    }

    public function getValue(string $dataGroupKey, string $valueKey): ?string
    {
        return $this->dataGroups->getValue($dataGroupKey, $valueKey);
    }

    /**
     * @psalm-return array<string, array<string, string|null>>
     */
    public function toArray(): array
    {
        return $this->dataGroups->toArray();
    }

    public function toString(Delimiter $delimiter): string
    {
        $string = '';

        foreach($this->dataGroups->toArray() as $dataGroup) {
            foreach ($dataGroup as $value) {
                $string .= $value === null
                    ? $delimiter->getData()
                    : $delimiter->terminate($value) . $delimiter->getData();
            }

            $string = $this->trimEmpty(
                $string, $delimiter->getData(), $delimiter->getTerminator()
            ) . $delimiter->getDataGroup();
        }

        return $this->trimEmpty($string, $delimiter->getDataGroup(), $delimiter->getTerminator());
    }

    private function trimEmpty(string $string, string $dataGroupSeperator, string $terminator): string
    {
        while(true) {
            if ($dataGroupSeperator !== $string[-1] ?? null) {
                break;
            }

            if ($terminator === $string[-2] ?? null) {
                break;
            }

            $string = substr($string, 0, -1);
        }

        return $string;
    }
}
