<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\DataGroups;

class Ucm extends AbstractSegment
{
    private static ?DataGroups $blueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new DataGroups)
                ->addValue('UCM', 'UCM', 'M|an|3')
                ->addValue('0062', '0062', 'M|an|14')
                ->addValue('S009', '0065', 'M|an|6')
                ->addValue('S009', '0052', 'M|an|3')
                ->addValue('S009', '0054', 'M|an|3')
                ->addValue('S009', '0051', 'M|an|2')
                ->addValue('S009', '0057', 'M|an|6')
                ->addValue('0083', '0083', 'M|n|1')
                ->addValue('0085', '0085', 'O|n|2')
                ->addValue('0013', '0013', 'O|a|3')
                ->addValue('S011', '0098', 'O|n|3')
                ->addValue('S011', '0104', 'O|n|3');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(
        string $referenz,
        string $type,
        string $versionNumber,
        string $releaseNumber,
        string $organisation,
        string $organisationCode,
        string $errorCode,
        ?string $serviceSegement = null,
        ?string $segmentPosition = null,
        ?string $dataGroupPosition = null,
    ): self {
        return new self((new DataGroups)
            ->addValue('UCM', 'UCM', 'UCM')
            ->addValue('0062', '0062', $referenz)
            ->addValue('S009', '0065', $type)
            ->addValue('S009', '0052', $versionNumber)
            ->addValue('S009', '0054', $releaseNumber)
            ->addValue('S009', '0051', $organisation)
            ->addValue('S009', '0057', $organisationCode)
            ->addValue('0083', '0083', '4')
            ->addValue('0085', '0085', $errorCode)
            ->addValue('0013', '0013', $serviceSegement)
            ->addValue('S011', '0098', $segmentPosition)
            ->addValue('S011', '0104', $dataGroupPosition)
        );
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

    public function status(): ?string
    {
        return $this->elements->getValue('0083', '0083');
    }

    /*
     * @Deprecated
     */
    public function error(): ?string
    {
        return $this->errorCode();
    }

    public function errorCode(): ?string
    {
        return $this->elements->getValue('0085', '0085');
    }

    public function serviceSegement(): ?string
    {
        return $this->elements->getValue('0013', '0013');
    }

    public function segmentPosition(): ?string
    {
        return $this->elements->getValue('S011', '0098');
    }

    public function dataGroupPosition(): ?string
    {
        return $this->elements->getValue('S011', '0104');
    }
}
