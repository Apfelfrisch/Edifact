<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Test\Fixtures;

use Apfelfrisch\Edifact\Segment\AbstractSegment;
use Apfelfrisch\Edifact\Segment\Elements;

final class Rff extends AbstractSegment
{
    private static ?Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        return self::$blueprint ??= new Elements()
            ->addValue('RFF', 'RFF', 'M|a|3')
            ->addValue('C506', '1153', 'M|an|3')
            ->addValue('C506', '1154', 'M|an|70');
    }

    public static function fromAttributes(string $code, ?string $reference = null): self
    {
        return new self(
            new Elements()
                ->addValue('RFF', 'RFF', 'RFF')
                ->addValue('C506', '1153', $code)
                ->addValue('C506', '1154', $reference),
        );
    }

    public function code(): ?string
    {
        return $this->elements->getValue('C506', '1153');
    }

    public function reference(): ?string
    {
        return $this->elements->getValue('C506', '1154');
    }
}
