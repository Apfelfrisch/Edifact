<?php

namespace Proengeno\Edifact\Message\Segments;

use Proengeno\Edifact\Message\DataGroups;
use Proengeno\Edifact\Message\SegmentData;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Templates\AbstractSegment;

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

    public static function fromAttributes(Delimiter $delimiter, string $id, string $type): self
    {
        return new self(new SegmentData(
            (new DataGroups)
                ->addValue('COM', 'COM', 'COM')
                ->addValue('C076', '3148', $id)
                ->addValue('C076', '3155', $type),
            $delimiter
        ));
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
