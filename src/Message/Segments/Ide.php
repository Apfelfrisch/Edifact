<?php

namespace Proengeno\Edifact\Message\Segments;

use Proengeno\Edifact\Message\DataGroups;
use Proengeno\Edifact\Message\SegmentData;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Templates\AbstractSegment;

class Ide extends AbstractSegment
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('IDE', 'IDE', 'M|a|3')
                ->addValue('7495', '7495', 'M|an|3')
                ->addValue('C206', '7402', 'M|an|35');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(Delimiter $delimiter, string $qualifier, string $idNumber): self
    {
        return new self(new SegmentData(
            (new DataGroups)
                ->addValue('IDE', 'IDE', 'IDE')
                ->addValue('7495', '7495', $qualifier)
                ->addValue('C206', '7402', $idNumber),
            $delimiter
        ));
    }

    public function qualifier(): ?string
    {
        return $this->elements->getValue('7495', '7495');
    }

    public function idNumber(): ?string
    {
        return $this->elements->getValue('C206', '7402');
    }
}
