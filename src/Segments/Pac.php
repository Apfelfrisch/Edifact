<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Elements;

class Pac extends AbstractSegment
{
    private static ?Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new Elements)
                ->addValue('PAC', 'PAC', 'M|a|3')
                ->addValue('7224', '7224', 'M|a|8')
                ->addValue('C531', '7075', 'M|an|3');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(string $quantity, string $code): self
    {
        return new self((new Elements)
            ->addValue('PAC', 'PAC', 'PAC')
            ->addValue('7224', '7224', $quantity)
            ->addValue('C531', '7075', $code)
        );
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
