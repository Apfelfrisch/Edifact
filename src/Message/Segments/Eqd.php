<?php

namespace Proengeno\Edifact\Message\Segments;

use Proengeno\Edifact\Message\DataGroups;
use Proengeno\Edifact\Message\SegmentData;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Templates\AbstractSegment;

class Eqd extends AbstractSegment
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('EQD', 'EQD', 'M|a|3')
                ->addValue('8053', '8053', 'M|an|3')
                ->addValue('C237', '8260', 'M|n|17');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(Delimiter $delimiter, string $qualifier, string $processNumber): self
    {
        return new self(new SegmentData(
            (new DataGroups)
                ->addValue('EQD', 'EQD', 'EQD')
                ->addValue('8053', '8053', $qualifier)
                ->addValue('C237', '8260', $processNumber),
            $delimiter
        ));
    }

    public function qualifier(): ?string
    {
        return $this->elements->getValue('8053', '8053');
    }

    public function processNumber(): ?string
    {
        return $this->elements->getValue('C237', '8260');
    }
}
