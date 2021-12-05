<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Elements;

class Pgi extends AbstractSegment
{
    private static ?Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new Elements)
                ->addValue('PGI', 'PGI', 'M|a|3')
                ->addValue('5379', '5379', 'M|an|..3');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(string $code): self
    {
        return new self((new Elements)
            ->addValue('PGI', 'PGI', 'PGI')
            ->addValue('5379', '5379', $code)
        );
    }

    public function code(): ?string
    {
        return $this->elements->getValue('5379', '5379');
    }
}
