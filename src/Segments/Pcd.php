<?php

namespace Proengeno\Edifact\Segments;

use Proengeno\Edifact\DataGroups;

class Pcd extends AbstractSegment
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('PCD', 'PCD', 'M|a|3')
                ->addValue('C501', '5245', 'M|an|3')
                ->addValue('C501', '5482', 'M|n|10');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(string $percent, string $qualifier = '3'): self
    {
        return new self((new DataGroups)
            ->addValue('PCD', 'PCD', 'PCD')
            ->addValue('C501', '5245', $qualifier)
            ->addValue('C501', '5482', $percent)
        );
    }

    public function qualifier(): ?string
    {
        return $this->elements->getValue('C501', '5245');
    }

    public function percent(): ?string
    {
        return $this->elements->getValue('C501', '5482');
    }
}
