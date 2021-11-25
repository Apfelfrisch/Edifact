<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\DataGroups;

class Pyt extends AbstractSegment
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('PYT', 'PYT', 'M|a|3')
                ->addValue('4279', '4279', 'M|n|3');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(string $qualifier): self
    {
        return new self((new DataGroups)
            ->addValue('PYT', 'PYT', 'PYT')
            ->addValue('4279', '4279', $qualifier)
        );
    }

    public function qualifier(): ?string
    {
        return $this->elements->getValue('4279', '4279');
    }
}
