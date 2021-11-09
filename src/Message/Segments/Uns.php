<?php

namespace Proengeno\Edifact\Message\Segments;

use Proengeno\Edifact\Message\DataGroups;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\SegmentData;
use Proengeno\Edifact\Templates\AbstractSegment;

class Uns extends AbstractSegment
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('UNS', 'UNS', 'M|a|3')
                ->addValue('0081', '0081', 'M|a|1');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(Delimiter $delimiter, string $code = 'S'): self
    {
        return new self(new SegmentData(
            (new DataGroups)
                ->addValue('UNS', 'UNS', 'UNS')
                ->addValue('0081', '0081', $code),
            $delimiter
        ));
    }

    public function code(): ?string
    {
        return $this->elements->getValue('0081', '0081');
    }
}
