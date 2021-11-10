<?php

namespace Proengeno\Edifact\Message\Segments;

use Proengeno\Edifact\Message\DataGroups;
use Proengeno\Edifact\Message\SegmentData;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Templates\AbstractSegment;

class Pia extends AbstractSegment
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('PIA', 'PIA', 'M|a|3')
                ->addValue('4347', '4347', 'M|n|3')
                ->addValue('C212', '7140', 'D|an|35')
                ->addValue('C212', '7143', 'D|an|3');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(Delimiter $delimiter, string $number, ?string $articleNumber = null, ?string $articleCode = null): self
    {
        return new self(new SegmentData(
            (new DataGroups)
                ->addValue('PIA', 'PIA', 'PIA')
                ->addValue('4347', '4347', $number)
                ->addValue('C212', '7140', $articleNumber)
                ->addValue('C212', '7143', $articleCode),
            $delimiter
        ));
    }

    public function number(): ?string
    {
        return $this->elements->getValue('4347', '4347');
    }

    public function articleNumber(): ?string
    {
        return $this->elements->getValue('C212', '7140');
    }

    public function articleCode(): ?string
    {
        return $this->elements->getValue('C212', '7143');
    }
}
