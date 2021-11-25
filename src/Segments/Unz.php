<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\DataGroups;

class Unz extends AbstractSegment
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('UNZ', 'UNZ', 'M|an|3')
                ->addValue('0062', '0062', 'M|n|6')
                ->addValue('S009', '0065', 'M|an|35');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(string $counter, string $referenz): self
    {
        return new self((new DataGroups)
            ->addValue('UNZ', 'UNZ', 'UNZ')
            ->addValue('0062', '0062', $counter)
            ->addValue('S009', '0065', $referenz)
        );
    }

    public function counter(): ?string
    {
        return $this->elements->getValue('0062', '0062');
    }

    public function referenz(): ?string
    {
        return $this->elements->getValue('S009', '0065');
    }
}
