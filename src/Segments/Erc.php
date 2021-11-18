<?php

namespace Proengeno\Edifact\Segments;

use Proengeno\Edifact\DataGroups;
use Proengeno\Edifact\SegmentData;

class Erc extends AbstractSegment
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('ERC', 'ERC', 'M|a|3')
                ->addValue('C901', '9321', 'M|an|8');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(string $error): self
    {
        return new self(new SegmentData(
            (new DataGroups)
                ->addValue('ERC', 'ERC', 'ERC')
                ->addValue('C901', '9321', $error)
        ));
    }

    public function errorCode(): ?string
    {
        return $this->elements->getValue('C901', '9321');
    }
}
