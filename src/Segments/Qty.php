<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Elements;

class Qty extends AbstractSegment
{
    private static ?Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new Elements)
                ->addValue('QTY', 'QTY', 'M|a|3')
                ->addValue('C186', '6063', 'M|an|..3')
                ->addValue('C186', '6060', 'M|an|..35')
                ->addValue('C186', '6411', 'O|an|..8');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(string $qualifier, string $amount, ?string $unitCode = null): self
    {
        return new self((new Elements)
            ->addValue('QTY', 'QTY', 'QTY')
            ->addValue('C186', '6063', $qualifier)
            ->addValue('C186', '6060', $amount)
            ->addValue('C186', '6411', $unitCode)
        );
    }

    public function qualifier(): ?string
    {
        return $this->elements->getValue('C186', '6063');
    }

    public function amount(): ?string
    {
        return $this->replaceDecimalPoint((string)$this->elements->getValue('C186', '6060'));
    }

    public function unitCode(): ?string
    {
        return $this->elements->getValue('C186', '6411');
    }
}
