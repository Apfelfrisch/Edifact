<?php

namespace Proengeno\Edifact\Segments;

use Proengeno\Edifact\DataGroups;
use Proengeno\Edifact\SegmentData;

class Cni extends AbstractSegment
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('CNI', 'CNI', 'M|a|3')
                ->addValue('1490', '1490', 'M|n|5');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(string $number): self
    {
        return new self(new SegmentData(
            (new DataGroups)
                ->addValue('CNI', 'CNI', 'CNI')
                ->addValue('1490', '1490', $number)
        ));
    }

    public function number(): ?string
    {
        return $this->elements->getValue('1490', '1490');
    }
}
