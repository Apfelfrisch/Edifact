<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Elements;

class Ucs extends AbstractSegment
{
    private static ?Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new Elements)
                ->addValue('UCS', 'UCS', 'M|an|3')
                ->addValue('0096', '0096', 'M|a|..6')
                ->addValue('0085', '0085', 'M|a|..2');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(string $position, string $error): self
    {
        return new self((new Elements)
            ->addValue('UCS', 'UCS', 'UCS')
            ->addValue('0096', '0096', $position)
            ->addValue('0085', '0085', $error)
        );
    }

    public function position(): ?string
    {
        return $this->elements->getValue('0096', '0096');
    }

    public function error(): ?string
    {
        return $this->elements->getValue('0085', '0085');
    }
}
