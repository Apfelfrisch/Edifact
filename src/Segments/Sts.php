<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Elements;

class Sts extends AbstractSegment
{
    private static ?Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new Elements)
                ->addValue('STS', 'STS', 'M|a|3')
                ->addValue('C601', '9015', 'O|an|..3')
                ->addValue('C555', '4405', 'O|an|..3')
                ->addValue('C556', '9013', 'O|an|..3')
                ->addValue('C556', '1131', 'O|an|..17');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(string $category, ?string $reason, ?string $code = null, ?string $status = null): self
    {
        return new self((new Elements)
            ->addValue('STS', 'STS', 'STS')
            ->addValue('C601', '9015', $category)
            ->addValue('C555', '4405', $status)
            ->addValue('C556', '9013', $reason)
            ->addValue('C556', '1131', $code)
        );
    }

    public function category(): ?string
    {
        return $this->elements->getValue('C601', '9015');
    }

    public function status(): ?string
    {
        return $this->elements->getValue('C555', '4405');
    }

    public function reason(): ?string
    {
        return $this->elements->getValue('C556', '9013');
    }

    public function code(): ?string
    {
        return $this->elements->getValue('C556', '1131');
    }
}
