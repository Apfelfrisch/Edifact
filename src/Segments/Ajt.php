<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Elements;

class Ajt extends AbstractSegment
{
    private static ?Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new Elements)
                ->addValue('AJT', 'AJT', 'M|a|3')
                ->addValue('4465', '4465', 'M|an|..3');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(string $code): self
    {
        return new self((new Elements)
            ->addValue('AJT', 'AJT', 'AJT')
            ->addValue('4465', '4465', $code)
        );
    }

    public function code(): ?string
    {
        return $this->elements->getValue('4465', '4465');
    }
}
