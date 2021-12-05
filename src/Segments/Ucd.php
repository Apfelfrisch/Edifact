<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Elements;

class Ucd extends AbstractSegment
{
    private static ?Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new Elements)
                ->addValue('UCD', 'UCD', 'M|an|3')
                ->addValue('0085', '0085', 'M|n|..2')
                ->addValue('S011', '0098', 'M|n|..3')
                ->addValue('S011', '0104', 'O|an|..3');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(string $errorCode, string $segmentPosition, ?string $elementPosition = null): self
    {
        return new self((new Elements)
            ->addValue('UCD', 'UCD', 'UCD')
            ->addValue('0085', '0085', $errorCode)
            ->addValue('S011', '0098', $segmentPosition)
            ->addValue('S011', '0104', $elementPosition)
        );
    }

    public function errorCode(): ?string
    {
        return $this->elements->getValue('0085', '0085');
    }

    public function segmentPosition(): ?string
    {
        return $this->elements->getValue('S011', '0098');
    }

    public function elementPosition(): ?string
    {
        return $this->elements->getValue('S011', '0104');
    }
}
