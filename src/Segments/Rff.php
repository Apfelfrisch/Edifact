<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Elements;

class Rff extends AbstractSegment
{
    private static ?Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new Elements)
                ->addValue('RFF', 'RFF', 'M|a|3')
                ->addValue('C506', '1153', 'M|an|3')
                ->addValue('C506', '1154', 'M|an|70');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(string $code, ?string $referenz = null): self
    {
        return new self((new Elements)
            ->addValue('RFF', 'RFF', 'RFF')
            ->addValue('C506', '1153', $code)
            ->addValue('C506', '1154', $referenz)
        );
    }

    public function code(): ?string
    {
        return $this->elements->getValue('C506', '1153');
    }

    public function referenz(): ?string
    {
        return $this->elements->getValue('C506', '1154');
    }
}
