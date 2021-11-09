<?php

namespace Proengeno\Edifact\Message\Segments;

use Proengeno\Edifact\Message\DataGroups;
use Proengeno\Edifact\Message\SegmentData;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Templates\AbstractSegment;

class Agr extends AbstractSegment
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('AGR', 'AGR', 'M|a|3')
                ->addValue('C543', '7431', 'M|an|3')
                ->addValue('C543', '7433', 'M|an|3');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(Delimiter $delimiter, string $qualifier, string $type): self
    {
        return new self(new SegmentData(
            (new DataGroups)
                ->addValue('AGR', 'AGR', 'AGR')
                ->addValue('C543', '7431', $qualifier)
                ->addValue('C543', '7433', $type),
            $delimiter
        ));
    }

    public function qualifier(): ?string
    {
        return $this->elements->getValue('C543', '7431');
    }

    public function type(): ?string
    {
        return $this->elements->getValue('C543', '7433');
    }
}
