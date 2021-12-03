<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Elements;

class Seq extends AbstractSegment
{
    private static ?Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new Elements)
                ->addValue('SEQ', 'SEQ', 'M|a|3')
                ->addValue('1229', '1229', 'M|an|..3');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(string $code): self
    {
        return new self((new Elements)
            ->addValue('SEQ', 'SEQ', 'SEQ')
            ->addValue('1229', '1229', $code)
        );
    }

    public function code(): ?string
    {
        return $this->elements->getValue('1229', '1229');
    }
}
