<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\DataGroups;

class Cci extends AbstractSegment
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('CCI', 'CCI', 'M|a|3')
                ->addValue('7059', '7059', 'O|an|3')
                ->addValue('C502', '6313', null)
                ->addValue('C240', '7037', 'O|an|17')
                ->addValue('C240', '1131', 'O|an|17')
                ->addValue('C240', '3055', 'O|an|3')
                ->addValue('C240', '7036', 'O|an|35');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(
        ?string $type = null,
        ?string $code = null,
        ?string $mark = null,
        ?string $codeList = null,
        ?string $codeResponsible = null
    ): self
    {
        return new self((new DataGroups)
            ->addValue('CCI', 'CCI', 'CCI')
            ->addValue('7059', '7059', $type)
            ->addValue('C502', '6313', null)
            ->addValue('C240', '7037', $code)
            ->addValue('C240', '1131', $codeList)
            ->addValue('C240', '3055', $codeResponsible)
            ->addValue('C240', '7036', $mark)
        );
    }

    public function type(): ?string
    {
        return $this->elements->getValue('7059', '7059');
    }

    public function code(): ?string
    {
        return $this->elements->getValue('C240', '7037');
    }

    public function codeList(): ?string
    {
        return $this->elements->getValue('C240', '1131');
    }

    public function codeResponsible(): ?string
    {
        return $this->elements->getValue('C240', '3055');
    }

    public function mark(): ?string
    {
        return $this->elements->getValue('C240', '7036');
    }
}
