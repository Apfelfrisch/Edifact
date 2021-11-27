<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Elements;

class Com extends AbstractSegment
{
    private static ?Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new Elements)
                ->addValue('COM', 'COM', 'M|a|3')
                ->addValue('C076', '3148', 'M|an|512')
                ->addValue('C076', '3155', 'M|an|3');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(string $id, string $type): self
    {
        return new self((new Elements)
            ->addValue('COM', 'COM', 'COM')
            ->addValue('C076', '3148', $id)
            ->addValue('C076', '3155', $type)
        );
    }

    public function id(): ?string
    {
        return $this->elements->getValue('C076', '3148');
    }

    public function type(): ?string
    {
        return $this->elements->getValue('C076', '3155');
    }
}
