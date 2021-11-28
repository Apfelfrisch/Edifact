<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Elements;

final class Cav extends AbstractSegment
{
    private static ?Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new Elements)
                ->addValue('CAV', 'CAV', 'M|a|3')
                ->addValue('C889', '7111', 'O|an|..3')
                ->addValue('C889', '1131', 'O|an|..17')
                ->addValue('C889', '3055', 'O|an|..3')
                ->addValue('C889', '7110:1', 'O|an|..35')
                ->addValue('C889', '7110:2', 'O|an|..35');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(
        ?string $code = null,
        ?string $responsCode = null,
        ?string $valueOne = null,
        ?string $valueTwo = null,
        ?string $codeList = null
    ): self
    {
        return new self((new Elements)
            ->addValue('CAV', 'CAV', 'CAV')
            ->addValue('C889', '7111', $code)
            ->addValue('C889', '1131', $codeList)
            ->addValue('C889', '3055', $responsCode)
            ->addValue('C889', '7110:1', $valueOne)
            ->addValue('C889', '7110:2', $valueTwo)
        );
    }

    public function code(): ?string
    {
        return $this->elements->getValue('C889', '7111');
    }

    public function codeList(): ?string
    {
        return $this->elements->getValue('C889', '1131');
    }

    public function responsCode(): ?string
    {
        return $this->elements->getValue('C889', '3055');
    }

    public function value(): ?string
    {
        return $this->valueOne();
    }

    public function valueOne(): ?string
    {
        return $this->elements->getValue('C889', '7110:1');
    }

    public function valueTwo(): ?string
    {
        return $this->elements->getValue('C889', '7110:2');
    }
}
