<?php

namespace Proengeno\Edifact\Message\Segments;

use Proengeno\Edifact\Message\DataGroups;
use Proengeno\Edifact\Message\SegmentData;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Templates\AbstractSegment;

class Loc extends AbstractSegment
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('LOC', 'LOC', 'M|a|3')
                ->addValue('3227', '3227', 'M|an|3')
                ->addValue('C517', '3225', 'M|an|35')
                ->addValue('C519', '3223', null)
                ->addValue('C553', '3233', null)
                ->addValue('5479', '5479', 'O|n|1');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(Delimiter $delimiter, string $qualifier, string $number, ?string $priority = null): self
    {
        return new self(new SegmentData(
            (new DataGroups)
                ->addValue('LOC', 'LOC', 'LOC')
                ->addValue('3227', '3227', $qualifier)
                ->addValue('C517', '3225', $number)
                ->addValue('C519', '3223', null)
                ->addValue('C553', '3233', null)
                ->addValue('5479', '5479', $priority),
            $delimiter
        ));
    }

    public function qualifier(): ?string
    {
        return $this->elements->getValue('3227', '3227');
    }

    public function number(): ?string
    {
        return $this->elements->getValue('C517', '3225');
    }

    public function priority(): ?string
    {
        return $this->elements->getValue('5479', '5479');
    }
}
