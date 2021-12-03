<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Elements;

class Pcd extends AbstractSegment
{
    private static ?Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new Elements)
                ->addValue('PCD', 'PCD', 'M|a|3')
                ->addValue('C501', '5245', 'M|an|..3')
                ->addValue('C501', '5482', 'M|n|..10');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(string $percent, string $qualifier = '3'): self
    {
        return new self((new Elements)
            ->addValue('PCD', 'PCD', 'PCD')
            ->addValue('C501', '5245', $qualifier)
            ->addValue('C501', '5482', $percent)
        );
    }

    public function qualifier(): ?string
    {
        return $this->elements->getValue('C501', '5245');
    }

    public function percent(): ?string
    {
        return $this->elements->getValue('C501', '5482');
    }
}
