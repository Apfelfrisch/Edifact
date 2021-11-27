<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Elements;

class Ide extends AbstractSegment
{
    private static ?Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new Elements)
                ->addValue('IDE', 'IDE', 'M|a|3')
                ->addValue('7495', '7495', 'M|an|3')
                ->addValue('C206', '7402', 'M|an|35');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(string $qualifier, string $idNumber): self
    {
        return new self((new Elements)
            ->addValue('IDE', 'IDE', 'IDE')
            ->addValue('7495', '7495', $qualifier)
            ->addValue('C206', '7402', $idNumber)
        );
    }

    public function qualifier(): ?string
    {
        return $this->elements->getValue('7495', '7495');
    }

    public function idNumber(): ?string
    {
        return $this->elements->getValue('C206', '7402');
    }
}
