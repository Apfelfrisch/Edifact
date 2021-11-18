<?php

namespace Proengeno\Edifact\Segments;

use Proengeno\Edifact\DataGroups;
use Proengeno\Edifact\SegmentData;

class Ucs extends AbstractSegment
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('UCS', 'UCS', 'M|an|3')
                ->addValue('0096', '0096', 'M|a|6')
                ->addValue('0085', '0085', 'M|a|2');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(string $position, string $error): self
    {
        return new self(new SegmentData(
            (new DataGroups)
                ->addValue('UCS', 'UCS', 'UCS')
                ->addValue('0096', '0096', $position)
                ->addValue('0085', '0085', $error)
        ));
    }

    public function position(): ?string
    {
        return $this->elements->getValue('0096', '0096');
    }

    public function error(): ?string
    {
        return $this->elements->getValue('0085', '0085');
    }
}
