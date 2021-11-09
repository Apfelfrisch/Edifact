<?php

namespace Proengeno\Edifact\Message\Segments;

use Proengeno\Edifact\Message\SegmentData;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\DataGroups;
use Proengeno\Edifact\Templates\AbstractSegment;

class Pgi extends AbstractSegment
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('PGI', 'PGI', 'M|a|3')
                ->addValue('5379', '5379', 'M|an|3');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(Delimiter $delimiter, string $code): self
    {
        return new self(new SegmentData(
            (new DataGroups)
                ->addValue('PGI', 'PGI', 'PGI')
                ->addValue('5379', '5379', $code),
            $delimiter
        ));
    }

    public function code(): ?string
    {
        return $this->elements->getValue('5379', '5379');
    }
}
