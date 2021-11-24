<?php

namespace Proengeno\Edifact\Segments;

use Proengeno\Edifact\DataGroups;

class Com extends AbstractSegment
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('COM', 'COM', 'M|a|3')
                ->addValue('C076', '3148', 'M|an|512')
                ->addValue('C076', '3155', 'M|an|3');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(string $id, string $type): self
    {
        return new self((new DataGroups)
            ->addValue('COM', 'COM', 'COM')
            ->addValue('C076', '3148', $id)
            ->addValue('C076', '3155', $type)
        );
    }

    public function id(): ?string
    {
        return $this->elements->getValue('C076', '3148');
    }

    public function type(): ?string
    {
        return $this->elements->getValue('C076', '3155');
    }
}
