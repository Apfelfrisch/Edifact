<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\DataGroups;

class Erc extends AbstractSegment
{
    private static ?DataGroups $blueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new DataGroups)
                ->addValue('ERC', 'ERC', 'M|a|3')
                ->addValue('C901', '9321', 'M|an|8');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(string $error): self
    {
        return new self((new DataGroups)
            ->addValue('ERC', 'ERC', 'ERC')
            ->addValue('C901', '9321', $error)
        );
    }

    public function errorCode(): ?string
    {
        return $this->elements->getValue('C901', '9321');
    }
}
