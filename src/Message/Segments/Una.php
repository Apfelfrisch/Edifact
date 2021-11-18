<?php

namespace Proengeno\Edifact\Message\Segments;

use Proengeno\Edifact\Message\DataGroups;
use Proengeno\Edifact\Message\SegmentData;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Templates\AbstractSegment;

class Una extends AbstractSegment
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('UNA', 'UNA', 'M|a|3')
                ->addValue('UNA', 'data', 'M|an|1')
                ->addValue('UNA', 'dataGroup', 'M|an|1')
                ->addValue('UNA', 'decimal', 'M|an|1')
                ->addValue('UNA', 'terminator', 'M|an|1')
                ->addValue('UNA', 'empty', 'M|an|1');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(
        Delimiter $delimiter,
        string $data = ':',
        string $dataGroup = '+',
        string $decimal = '.',
        string $terminator = '?',
        string $empty = ' '
    ): self
    {
        return new self(new SegmentData(
            (new DataGroups)
                ->addValue('UNA', 'UNA', 'UNA')
                ->addValue('UNA', 'data', $data)
                ->addValue('UNA', 'dataGroup', $dataGroup)
                ->addValue('UNA', 'decimal', $decimal)
                ->addValue('UNA', 'terminator', $terminator)
                ->addValue('UNA', 'empty', $empty),
            $delimiter
        ));
    }

    public function data(): ?string
    {
        return $this->elements->getValue('UNA', 'data');
    }

    public function dataGroup(): ?string
    {
        return $this->elements->getValue('UNA', 'dataGroup');
    }

    public function decimal(): ?string
    {
        return $this->elements->getValue('UNA', 'decimal');
    }

    public function terminator(): ?string
    {
        return $this->elements->getValue('UNA', 'terminator');
    }

    public function emptyChar(): ?string
    {
        return $this->elements->getValue('UNA', 'empty');
    }

    public function toString(): string
    {
        return $this->elements->getValue('UNA', 'UNA')
            . $this->elements->getValue('UNA', 'data')
            . $this->elements->getValue('UNA', 'dataGroup')
            . $this->elements->getValue('UNA', 'decimal')
            . $this->elements->getValue('UNA', 'terminator')
            . $this->elements->getValue('UNA', 'empty');
    }

    protected static function mapToBlueprint(Delimiter $delimiter, string $segLine): SegmentData
    {
        $inputElement = ['UNA'] + str_split(substr($segLine, 2));
        $dataCollection = new DataGroups;

        $i = 0;
        foreach (array_keys(self::blueprint()->getDataGroup('UNA')) as $BpDataKey) {
            $dataCollection->addValue('UNA', $BpDataKey, $inputElement[$i] ?? null);
            $i++;
        }

        return new SegmentData($dataCollection, $delimiter);
    }
}
