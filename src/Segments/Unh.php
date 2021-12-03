<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Elements;

class Unh extends AbstractSegment
{
    private static ?Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new Elements)
                ->addValue('UNH', 'UNH', 'M|an|3')
                ->addValue('0062', '0062', 'M|an|..14')
                ->addValue('S009', '0065', 'M|an|..6')
                ->addValue('S009', '0052', 'M|an|..3')
                ->addValue('S009', '0054', 'M|an|..3')
                ->addValue('S009', '0051', 'M|an|..2')
                ->addValue('S009', '0057', 'M|an|..6');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(
        string $referenz,
        string $type,
        string $versionNumber,
        string $releaseNumber,
        string $organisation,
        string $organisationCode
    ): self
    {
        return new self((new Elements)
            ->addValue('UNH', 'UNH', 'UNH')
            ->addValue('0062', '0062', $referenz)
            ->addValue('S009', '0065', $type)
            ->addValue('S009', '0052', $versionNumber)
            ->addValue('S009', '0054', $releaseNumber)
            ->addValue('S009', '0051', $organisation)
            ->addValue('S009', '0057', $organisationCode)
        );
    }

    public function reference(): string
    {
        return $this->referenz() ?? '';
    }

    public function referenz(): ?string
    {
        return $this->elements->getValue('0062', '0062');
    }

    public function type(): ?string
    {
        return $this->elements->getValue('S009', '0065');
    }

    public function versionNumber(): ?string
    {
        return $this->elements->getValue('S009', '0052');
    }

    public function releaseNumber(): ?string
    {
        return $this->elements->getValue('S009', '0054');
    }

    public function organisation(): ?string
    {
        return $this->elements->getValue('S009', '0051');
    }

    public function organisationCode(): ?string
    {
        return $this->elements->getValue('S009', '0057');
    }
}
