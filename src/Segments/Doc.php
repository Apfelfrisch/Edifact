<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Elements;

class Doc extends AbstractSegment
{
    private static ?Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new Elements)
                ->addValue('DOC', 'DOC', 'M|a|3')
                ->addValue('C002', '1001', 'M|an|..3')
                ->addValue('C503', '1004', 'M|an|..35');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(string $code, string $number): self
    {
        return new self((new Elements)
            ->addValue('DOC', 'DOC', 'DOC')
            ->addValue('C002', '1001', $code)
            ->addValue('C503', '1004', $number)
        );
    }

    public function code(): ?string
    {
        return $this->elements->getValue('C002', '1001');
    }

    public function number(): ?string
    {
        return $this->elements->getValue('C503', '1004');
    }
}
