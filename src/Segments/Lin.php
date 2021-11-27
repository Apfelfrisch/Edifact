<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Elements;

class Lin extends AbstractSegment
{
    private static ?Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new Elements)
                ->addValue('LIN', 'LIN', 'M|a|3')
                ->addValue('1082', '1082', 'M|n|6')
                ->addValue('1229', '1229', null)
                ->addValue('C212', '7140', 'D|an|35')
                ->addValue('C212', '7143', 'D|an|3');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(string $number, ?string $articleNumber = null, ?string $articleCode = null): self
    {
        return new self((new Elements)
            ->addValue('LIN', 'LIN', 'LIN')
            ->addValue('1082', '1082', $number)
            ->addValue('1229', '1229', null)
            ->addValue('C212', '7140', $articleNumber)
            ->addValue('C212', '7143', $articleCode)
        );
    }

    public function number(): ?string
    {
        return $this->elements->getValue('1082', '1082');
    }

    public function articleNumber(): ?string
    {
        return $this->elements->getValue('C212', '7140');
    }

    public function articleCode(): ?string
    {
        return $this->elements->getValue('C212', '7143');
    }
}
