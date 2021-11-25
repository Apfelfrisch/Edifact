<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\DataGroups;

class Alc extends AbstractSegment
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('ALC', 'ALC', 'M|a|3')
                ->addValue('5463', '5463', 'M|an|3')
                ->addValue('C552', '1230', null)
                ->addValue('C552', '5189', 'M|an|3');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(string $qualifier, string $code): self
    {
        return new self((new DataGroups)
            ->addValue('ALC', 'ALC', 'ALC')
            ->addValue('5463', '5463', $qualifier)
            ->addValue('C552', '1230', null)
            ->addValue('C552', '5189', $code)
        );
    }

    public function qualifier(): ?string
    {
        return $this->elements->getValue('5463', '5463');
    }

    public function code(): ?string
    {
        return $this->elements->getValue('C552', '5189');
    }
}
