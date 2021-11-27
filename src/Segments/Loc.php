<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Elements;

class Loc extends AbstractSegment
{
    private static ?Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new Elements)
                ->addValue('LOC', 'LOC', 'M|a|3')
                ->addValue('3227', '3227', 'M|an|3')
                ->addValue('C517', '3225', 'M|an|35')
                ->addValue('C519', '3223', null)
                ->addValue('C553', '3233', null)
                ->addValue('5479', '5479', 'O|n|1');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(string $qualifier, string $number, ?string $priority = null): self
    {
        return new self((new Elements)
            ->addValue('LOC', 'LOC', 'LOC')
            ->addValue('3227', '3227', $qualifier)
            ->addValue('C517', '3225', $number)
            ->addValue('C519', '3223', null)
            ->addValue('C553', '3233', null)
            ->addValue('5479', '5479', $priority)
        );
    }

    public function qualifier(): ?string
    {
        return $this->elements->getValue('3227', '3227');
    }

    public function number(): ?string
    {
        return $this->elements->getValue('C517', '3225');
    }

    public function priority(): ?string
    {
        return $this->elements->getValue('5479', '5479');
    }
}
