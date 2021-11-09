<?php

namespace Proengeno\Edifact\Message\Segments;

use Proengeno\Edifact\Message\DataGroups;
use Proengeno\Edifact\Message\SegmentData;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Templates\AbstractSegment;

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

    public static function fromAttributes(Delimiter $delimiter, string $number): self
    {
        return new self(new SegmentData(
            (new DataGroups)
                ->addValue('CNI', 'CNI', 'CNI')
                ->addValue('1490', '1490', $number),
            $delimiter
        ));
    }

    public function number(): ?string
    {
        return $this->elements->getValue('1490', '1490');
    }
}
