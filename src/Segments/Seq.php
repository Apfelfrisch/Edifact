<?php

namespace Proengeno\Edifact\Segments;

use Proengeno\Edifact\DataGroups;
use Proengeno\Edifact\SegmentData;

class Seq extends AbstractSegment
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('SEQ', 'SEQ', 'M|a|3')
                ->addValue('1229', '1229', 'M|an|3');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(string $code): self
    {
        return new self(new SegmentData(
            (new DataGroups)
            ->addValue('SEQ', 'SEQ', 'SEQ')
            ->addValue('1229', '1229', $code)
        ));
    }

    public function code(): ?string
    {
        return $this->elements->getValue('1229', '1229');
    }
}
