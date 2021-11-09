<?php

namespace Proengeno\Edifact\Message\Segments;

use Proengeno\Edifact\Message\DataGroups;
use Proengeno\Edifact\Message\SegmentData;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Templates\AbstractSegment;

class Ucd extends AbstractSegment
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('UCD', 'UCD', 'M|an|3')
                ->addValue('0085', '0085', 'M|n|2')
                ->addValue('S011', '0098', 'M|n|3')
                ->addValue('S011', '0104', 'O|an|3');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(Delimiter $delimiter, string $errorCode, string $segmentPosition, ?string $dataGroupPosition = null): self
    {
        return new self(new SegmentData(
            (new DataGroups)
                ->addValue('UCD', 'UCD', 'UCD')
                ->addValue('0085', '0085', $errorCode)
                ->addValue('S011', '0098', $segmentPosition)
                ->addValue('S011', '0104', $dataGroupPosition),
            $delimiter
        ));
    }

    public function errorCode(): ?string
    {
        return $this->elements->getValue('0085', '0085');
    }

    public function segmentPosition(): ?string
    {
        return $this->elements->getValue('S011', '0098');
    }

    public function dataGroupPosition(): ?string
    {
        return $this->elements->getValue('S011', '0104');
    }
}
