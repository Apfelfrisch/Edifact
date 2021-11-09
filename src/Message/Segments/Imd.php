<?php

namespace Proengeno\Edifact\Message\Segments;

use Proengeno\Edifact\Message\DataGroups;
use Proengeno\Edifact\Message\SegmentData;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Templates\AbstractSegment;

class Imd extends AbstractSegment
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('IMD', 'IMD', 'M|a|3')
                ->addValue('7077', '7077', null)
                ->addValue('C272', '7081', 'M|an|3')
                ->addValue('C273', '7009', 'O|an|17');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(Delimiter $delimiter, string $code, ?string $qualifier = null): self
    {
        return new self(new SegmentData(
            (new DataGroups)
                ->addValue('IMD', 'IMD', 'IMD')
                ->addValue('7077', '7077', null)
                ->addValue('C272', '7081', $code)
                ->addValue('C273', '7009', $qualifier),
            $delimiter
        ));
    }

    public function code(): ?string
    {
        return $this->elements->getValue('C272', '7081');
    }

    public function qualifier(): ?string
    {
        return $this->elements->getValue('C273', '7009');
    }
}
