<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Elements;

class Eqd extends AbstractSegment
{
    private static ?Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new Elements)
                ->addValue('EQD', 'EQD', 'M|a|3')
                ->addValue('8053', '8053', 'M|an|3')
                ->addValue('C237', '8260', 'M|n|17');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(string $qualifier, string $processNumber): self
    {
        return new self((new Elements)
            ->addValue('EQD', 'EQD', 'EQD')
            ->addValue('8053', '8053', $qualifier)
            ->addValue('C237', '8260', $processNumber)
        );
    }

    public function qualifier(): ?string
    {
        return $this->elements->getValue('8053', '8053');
    }

    public function processNumber(): ?string
    {
        return $this->elements->getValue('C237', '8260');
    }
}
