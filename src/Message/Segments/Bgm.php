<?php

namespace Proengeno\Edifact\Message\Segments;

use Proengeno\Edifact\Message\DataGroups;
use Proengeno\Edifact\Message\SegmentData;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Templates\AbstractSegment;

class Bgm extends AbstractSegment
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('BGM', 'BGM', 'M|a|3')
                ->addValue('C002', '1001', 'M|an|3')
                ->addValue('C106', '1004', 'M|an|35')
                ->addValue('1225', '1225', 'O|an|3');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(Delimiter $delimiter, string $docCode, string $docNumber, ?string $messageCode = null): self
    {
        return new self(new SegmentData(
            (new DataGroups)
                ->addValue('BGM', 'BGM', 'BGM')
                ->addValue('C002', '1001', $docCode)
                ->addValue('C106', '1004', $docNumber)
                ->addValue('1225', '1225', $messageCode),
            $delimiter
        ));
    }

    public function docCode(): ?string
    {
        return $this->elements->getValue('C002', '1001');
    }

    public function docNumber(): ?string
    {
        return $this->elements->getValue('C106', '1004');
    }

    public function messageCode(): ?string
    {
        return $this->elements->getValue('1225', '1225');
    }
}