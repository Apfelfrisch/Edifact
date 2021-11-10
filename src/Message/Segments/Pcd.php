<?php

namespace Proengeno\Edifact\Message\Segments;

use Proengeno\Edifact\Message\DataGroups;
use Proengeno\Edifact\Message\SegmentData;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Templates\AbstractSegment;

class Pcd extends AbstractSegment
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('PCD', 'PCD', 'M|a|3')
                ->addValue('C501', '5245', 'M|an|3')
                ->addValue('C501', '5482', 'M|n|10');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(Delimiter $delimiter, string $percent, string $qualifier = '3'): self
    {
        return new self(new SegmentData(
            (new DataGroups)
                ->addValue('PCD', 'PCD', 'PCD')
                ->addValue('C501', '5245', $qualifier)
                ->addValue('C501', '5482', $percent),
            $delimiter
        ));
    }

    public function qualifier(): ?string
    {
        return $this->elements->getValue('C501', '5245');
    }

    public function percent(): ?string
    {
        return $this->elements->getValue('C501', '5482');
    }
}
