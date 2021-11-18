<?php

namespace Proengeno\Edifact\Segments;

use Proengeno\Edifact\DataGroups;
use Proengeno\Edifact\SegmentData;

class Cta extends AbstractSegment
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('CTA', 'CTA', 'M|a|3')
                ->addValue('3139', '3139', 'M|an|3')
                ->addValue('C056', '3413', null)
                ->addValue('C056', '3412','M|an|35');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(string $type, string $employee): self
    {
        return new self(new SegmentData(
            (new DataGroups)
                ->addValue('CTA', 'CTA', 'CTA')
                ->addValue('3139', '3139', $type)
                ->addValue('C056', '3413', null)
                ->addValue('C056', '3412', $employee)
        ));
    }

    public function type(): ?string
    {
        return $this->elements->getValue('3139', '3139');
    }

    public function employee(): ?string
    {
        return $this->elements->getValue('C056', '3412');
    }
}
