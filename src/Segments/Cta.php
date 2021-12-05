<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Elements;

class Cta extends AbstractSegment
{
    private static ?Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new Elements)
                ->addValue('CTA', 'CTA', 'M|a|3')
                ->addValue('3139', '3139', 'M|an|..3')
                ->addValue('C056', '3413', null)
                ->addValue('C056', '3412','M|an|..35');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(string $type, string $employee): self
    {
        return new self((new Elements)
            ->addValue('CTA', 'CTA', 'CTA')
            ->addValue('3139', '3139', $type)
            ->addValue('C056', '3413', null)
            ->addValue('C056', '3412', $employee)
        );
    }

    public function type(): ?string
    {
        return $this->elements->getValue('3139', '3139');
    }

    public function employee(): ?string
    {
        return $this->elements->getValue('C056', '3412');
    }
}
