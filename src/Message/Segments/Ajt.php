<?php

namespace Proengeno\Edifact\Message\Segments;

use Proengeno\Edifact\Message\DataGroups;
use Proengeno\Edifact\Message\SegmentData;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Templates\AbstractSegment;

class Ajt extends AbstractSegment
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('AJT', 'AJT', 'M|a|3')
                ->addValue('4465', '4465', 'M|an|3');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(Delimiter $delimiter, string $code): self
    {
        return new self(new SegmentData(
            (new DataGroups)
                ->addValue('AJT', 'AJT', 'AJT')
                ->addValue('4465', '4465', $code),
            $delimiter
        ));
    }

    public function code(): ?string
    {
        return $this->elements->getValue('4465', '4465');
    }
}
