<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Elements;

class Pia extends AbstractSegment
{
    private static ?Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new Elements)
                ->addValue('PIA', 'PIA', 'M|a|3')
                ->addValue('4347', '4347', 'M|n|..3')
                ->addValue('C212', '7140', 'O|an|..35')
                ->addValue('C212', '7143', 'O|an|..3');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(string $number, ?string $articleNumber = null, ?string $articleCode = null): self
    {
        return new self((new Elements)
            ->addValue('PIA', 'PIA', 'PIA')
            ->addValue('4347', '4347', $number)
            ->addValue('C212', '7140', $articleNumber)
            ->addValue('C212', '7143', $articleCode)
        );
    }

    public function number(): ?string
    {
        return $this->elements->getValue('4347', '4347');
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
