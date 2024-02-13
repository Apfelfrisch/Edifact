<?php

namespace Apfelfrisch\Edifact\Test\Fixtures;

use Apfelfrisch\Edifact\Segment\AbstractSegment;
use Apfelfrisch\Edifact\Segment\Elements;

class Moa extends AbstractSegment
{
    private static ?Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new Elements())
                ->addValue('MOA', 'MOA', 'M|a|3')
                ->addValue('C516', '5025', 'M|an|3')
                ->addValue('C516', '5004', 'M|n|35');
        }

        return self::$blueprint;
    }

    /**
     * @todo $amount to string, remove $decimalSeperator: Its not the buisness of this class
     */
    public static function fromAttributes(string $qualifier, float $amount, string $decimalSeperator = '.'): self
    {
        return new self(
            (new Elements())
            ->addValue('MOA', 'MOA', 'MOA')
            ->addValue('C516', '5025', $qualifier)
            ->addValue('C516', '5004', number_format($amount, 2, $decimalSeperator, ''))
        );
    }

    public function qualifier(): ?string
    {
        return $this->elements->getValue('C516', '5025');
    }

    public function amount(): ?string
    {
        return $this->getValue('C516', '5004');
    }
}
