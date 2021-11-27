<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Elements;

class Agr extends AbstractSegment
{
    private static ?Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new Elements)
                ->addValue('AGR', 'AGR', 'M|a|3')
                ->addValue('C543', '7431', 'M|an|3')
                ->addValue('C543', '7433', 'M|an|3');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(string $qualifier, string $type): self
    {
        return new self((new Elements)
            ->addValue('AGR', 'AGR', 'AGR')
            ->addValue('C543', '7431', $qualifier)
            ->addValue('C543', '7433', $type)
        );
    }

    public function qualifier(): ?string
    {
        return $this->elements->getValue('C543', '7431');
    }

    public function type(): ?string
    {
        return $this->elements->getValue('C543', '7433');
    }
}
