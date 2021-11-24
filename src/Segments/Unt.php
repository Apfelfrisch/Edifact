<?php

namespace Proengeno\Edifact\Segments;

use Proengeno\Edifact\DataGroups;

class Unt extends AbstractSegment
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('UNT', 'UNT', 'M|a|3')
                ->addValue('0074', '0074', 'M|n|6')
                ->addValue('0062', '0062', 'M|an|15');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(string $segCount, string $referenz): self
    {
        return new self((new DataGroups)
            ->addValue('UNT', 'UNT', 'UNT')
            ->addValue('0074', '0074', $segCount)
            ->addValue('0062', '0062', $referenz)
        );
    }

    public function segCount(): ?string
    {
        return $this->elements->getValue('0074', '0074');
    }

    public function referenz(): ?string
    {
        return $this->elements->getValue('0062', '0062');
    }
}
