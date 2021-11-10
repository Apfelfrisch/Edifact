<?php

namespace Proengeno\Edifact\Message\Segments;

use Proengeno\Edifact\Message\DataGroups;
use Proengeno\Edifact\Message\SegmentData;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Templates\AbstractSegment;

class Pac extends AbstractSegment
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('PAC', 'PAC', 'M|a|3')
                ->addValue('7224', '7224', 'M|a|8')
                ->addValue('C531', '7075', 'M|an|3');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(Delimiter $delimiter, string $quantity, string $code): self
    {
        return new self(new SegmentData(
            (new DataGroups)
                ->addValue('PAC', 'PAC', 'PAC')
                ->addValue('7224', '7224', $quantity)
                ->addValue('C531', '7075', $code),
            $delimiter
        ));
    }

    public function quantity(): ?string
    {
        return $this->elements->getValue('7224', '7224');
    }

    public function code(): ?string
    {
        return $this->elements->getValue('C531', '7075');
    }
}
