<?php

namespace Proengeno\Edifact\Message\Segments;

use Proengeno\Edifact\Message\DataGroups;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\SegmentData;
use Proengeno\Edifact\Templates\AbstractSegment;

class Rff extends AbstractSegment
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('RFF', 'RFF', 'M|a|3')
                ->addValue('C506', '1153', 'M|an|3')
                ->addValue('C506', '1154', 'M|an|70');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(Delimiter $delimiter, string $code, ?string $referenz = null): self
    {
        return new self(new SegmentData(
            (new DataGroups)
                ->addValue('RFF', 'RFF', 'RFF')
                ->addValue('C506', '1153', $code)
                ->addValue('C506', '1154', $referenz),
            $delimiter
        ));
    }

    public function code(): ?string
    {
        return $this->elements->getValue('C506', '1153');
    }

    public function referenz(): ?string
    {
        return $this->elements->getValue('C506', '1154');
    }
}
