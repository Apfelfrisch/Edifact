<?php

namespace Proengeno\Edifact\Message\Segments;

use Proengeno\Edifact\Message\DataGroups;
use Proengeno\Edifact\Message\SegmentData;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Templates\AbstractSegment;

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

    public static function fromAttributes(Delimiter $delimiter, string $code): self
    {
        return new self(new SegmentData(
            (new DataGroups)
            ->addValue('SEQ', 'SEQ', 'SEQ')
            ->addValue('1229', '1229', $code),
            $delimiter
        ));
    }

    public function code(): ?string
    {
        return $this->elements->getValue('1229', '1229');
    }
}
