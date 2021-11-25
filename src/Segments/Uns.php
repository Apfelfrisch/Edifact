<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\DataGroups;

class Uns extends AbstractSegment
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('UNS', 'UNS', 'M|a|3')
                ->addValue('0081', '0081', 'M|a|1');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(string $code = 'S'): self
    {
        return new self((new DataGroups)
            ->addValue('UNS', 'UNS', 'UNS')
            ->addValue('0081', '0081', $code)
        );
    }

    public function code(): ?string
    {
        return $this->elements->getValue('0081', '0081');
    }
}
