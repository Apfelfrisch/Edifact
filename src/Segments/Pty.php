<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Elements;

class Pty extends AbstractSegment
{
    private static ?Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new Elements)
                ->addValue('PTY', 'PTY', 'M|a|3')
                ->addValue('4035', '4035', 'M|an|3')
                ->addValue('C585', '4037', null)
                ->addValue('C585', '1131', null)
                ->addValue('C585', '3055', null)
                ->addValue('C585', '4036', 'M|n|35');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(string $qualifier, string $priority): self
    {
        return new self((new Elements)
            ->addValue('PTY', 'PTY', 'PTY')
            ->addValue('4035', '4035', $qualifier)
            ->addValue('C585', '4037', null)
            ->addValue('C585', '1131', null)
            ->addValue('C585', '3055', null)
            ->addValue('C585', '4036', $priority)
        );
    }

    public function qualifier(): ?string
    {
        return $this->elements->getValue('4035', '4035');
    }

    public function priority(): ?string
    {
        return $this->elements->getValue('C585', '4036');
    }
}
