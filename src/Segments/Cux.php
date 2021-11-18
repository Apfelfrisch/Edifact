<?php

namespace Proengeno\Edifact\Segments;

use Proengeno\Edifact\DataGroups;
use Proengeno\Edifact\SegmentData;

class Cux extends AbstractSegment
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('CUX', 'CUX', 'M|a|3')
                ->addValue('C504', '6347', 'M|an|3')
                ->addValue('C504', '6345', 'M|an|3')
                ->addValue('C504', '6343', 'M|an|3');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(string $type, string $currency, string $qualifier): self
    {
        return new self(new SegmentData(
            (new DataGroups)
                ->addValue('CUX', 'CUX', 'CUX')
                ->addValue('C504', '6347', $type)
                ->addValue('C504', '6345', $currency)
                ->addValue('C504', '6343', $qualifier)
        ));
    }

    public function type(): ?string
    {
        return $this->elements->getValue('C504', '6347');
    }

    public function currency(): ?string
    {
        return $this->elements->getValue('C504', '6345');
    }

    public function qualifier(): ?string
    {
        return $this->elements->getValue('C504', '6343');
    }
}
