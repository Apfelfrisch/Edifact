<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Elements;

class Uns extends AbstractSegment
{
    private static ?Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new Elements)
                ->addValue('UNS', 'UNS', 'M|a|3')
                ->addValue('0081', '0081', 'M|a|1');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(string $code = 'S'): self
    {
        return new self((new Elements)
            ->addValue('UNS', 'UNS', 'UNS')
            ->addValue('0081', '0081', $code)
        );
    }

    public function code(): ?string
    {
        return $this->elements->getValue('0081', '0081');
    }
}
