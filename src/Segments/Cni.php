<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Elements;

class Cni extends AbstractSegment
{
    private static ?Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new Elements)
                ->addValue('CNI', 'CNI', 'M|a|3')
                ->addValue('1490', '1490', 'M|n|..5');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(string $number): self
    {
        return new self((new Elements)
            ->addValue('CNI', 'CNI', 'CNI')
            ->addValue('1490', '1490', $number)
        );
    }

    public function number(): ?string
    {
        return $this->elements->getValue('1490', '1490');
    }
}
