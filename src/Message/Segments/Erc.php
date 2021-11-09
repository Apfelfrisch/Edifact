<?php

namespace Proengeno\Edifact\Message\Segments;

use Proengeno\Edifact\Message\DataGroups;
use Proengeno\Edifact\Message\SegmentData;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Templates\AbstractSegment;

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

    public static function fromAttributes(Delimiter $delimiter, string $error): self
    {
        return new self(new SegmentData(
            (new DataGroups)
                ->addValue('ERC', 'ERC', 'ERC')
                ->addValue('C901', '9321', $error),
            $delimiter
        ));
    }

    public function errorCode(): ?string
    {
        return $this->elements->getValue('C901', '9321');
    }
}
