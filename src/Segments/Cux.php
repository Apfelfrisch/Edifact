<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Elements;

class Cux extends AbstractSegment
{
    private static ?Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new Elements)
                ->addValue('CUX', 'CUX', 'M|a|3')
                ->addValue('C504', '6347', 'M|an|..3')
                ->addValue('C504', '6345', 'M|an|..3')
                ->addValue('C504', '6343', 'M|an|..3');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(string $type, string $currency, string $qualifier): self
    {
        return new self((new Elements)
            ->addValue('CUX', 'CUX', 'CUX')
            ->addValue('C504', '6347', $type)
            ->addValue('C504', '6345', $currency)
            ->addValue('C504', '6343', $qualifier)
        );
    }

    public function type(): ?string
    {
        return $this->elements->getValue('C504', '6347');
    }

    public function currency(): ?string
    {
        return $this->elements->getValue('C504', '6345');
    }

    public function qualifier(): ?string
    {
        return $this->elements->getValue('C504', '6343');
    }
}
