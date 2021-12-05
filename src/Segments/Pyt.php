<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Elements;

class Pyt extends AbstractSegment
{
    private static ?Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new Elements)
                ->addValue('PYT', 'PYT', 'M|a|3')
                ->addValue('4279', '4279', 'M|n|..3');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(string $qualifier): self
    {
        return new self((new Elements)
            ->addValue('PYT', 'PYT', 'PYT')
            ->addValue('4279', '4279', $qualifier)
        );
    }

    public function qualifier(): ?string
    {
        return $this->elements->getValue('4279', '4279');
    }
}
