<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Elements;

class Bgm extends AbstractSegment
{
    private static ?Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new Elements)
                ->addValue('BGM', 'BGM', 'M|a|3')
                ->addValue('C002', '1001', 'M|an|3')
                ->addValue('C106', '1004', 'M|an|35')
                ->addValue('1225', '1225', 'O|an|3');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(string $docCode, string $docNumber, ?string $messageCode = null): self
    {
        return new self((new Elements)
            ->addValue('BGM', 'BGM', 'BGM')
            ->addValue('C002', '1001', $docCode)
            ->addValue('C106', '1004', $docNumber)
            ->addValue('1225', '1225', $messageCode)
        );
    }

    public function docCode(): ?string
    {
        return $this->elements->getValue('C002', '1001');
    }

    public function docNumber(): ?string
    {
        return $this->elements->getValue('C106', '1004');
    }

    public function messageCode(): ?string
    {
        return $this->elements->getValue('1225', '1225');
    }
}
