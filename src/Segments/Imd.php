<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Elements;

class Imd extends AbstractSegment
{
    private static ?Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new Elements)
                ->addValue('IMD', 'IMD', 'M|a|3')
                ->addValue('7077', '7077', null)
                ->addValue('C272', '7081', 'M|an|..3')
                ->addValue('C273', '7009', 'O|an|..17');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(string $code, ?string $qualifier = null): self
    {
        return new self((new Elements)
            ->addValue('IMD', 'IMD', 'IMD')
            ->addValue('7077', '7077', null)
            ->addValue('C272', '7081', $code)
            ->addValue('C273', '7009', $qualifier)
        );
    }

    public function code(): ?string
    {
        return $this->elements->getValue('C272', '7081');
    }

    public function qualifier(): ?string
    {
        return $this->elements->getValue('C273', '7009');
    }
}
