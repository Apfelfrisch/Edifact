<?php

namespace Apfelfrisch\Edifact\Segments;

use DateTime;
use Apfelfrisch\Edifact\DataGroups;
use Apfelfrisch\Edifact\Interfaces\UnbInterface;

class Unb extends AbstractSegment implements UnbInterface
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('UNB', 'UNB', 'M|an|3')
                ->addValue('S001', '0001', 'M|a|4')
                ->addValue('S001', '0002', 'm|n|1')
                ->addValue('S002', '0004', 'M|an|35')
                ->addValue('S002', '0007', 'M|an|4')
                ->addValue('S003', '0010', 'M|an|35')
                ->addValue('S003', '0007', 'M|an|4')
                ->addValue('S004', '0017', 'M|n|6')
                ->addValue('S004', '0019', 'M|n|4')
                ->addValue('0020', '0020', 'M|an|14')
                ->addValue('S005', '0022', null)
                ->addValue('0026', '0026', 'O|an|14')
                ->addValue('0029', '0029', null)
                ->addValue('0031', '0031', null)
                ->addValue('0032', '0032', null)
                ->addValue('0035', '0035', 'O|n|1');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(
        string $syntaxId,
        string $syntaxVersion,
        string $sender,
        string $senderQualifier,
        string $receiver,
        string $receiverQualifier,
        DateTime $creationDatetime,
        string $referenzNumber,
        ?string $usageType = null,
        ?string $testMarker = null
    ): self {
        return new self((new DataGroups)
            ->addValue('UNB', 'UNB', 'UNB')
            ->addValue('S001', '0001', $syntaxId)
            ->addValue('S001', '0002', $syntaxVersion)
            ->addValue('S002', '0004', $sender)
            ->addValue('S002', '0007', $senderQualifier)
            ->addValue('S003', '0010', $receiver)
            ->addValue('S003', '0007', $receiverQualifier)
            ->addValue('S004', '0017', $creationDatetime->format('ymd'))
            ->addValue('S004', '0019', $creationDatetime->format('Hi'))
            ->addValue('0020', '0020', $referenzNumber)
            ->addValue('0005', '0005', null)
            ->addValue('0026', '0026', $usageType)
            ->addValue('0029', '0029', null)
            ->addValue('0032', '0032', null)
            ->addValue('0035', '0035', $testMarker)
        );
    }

    public function reference(): string
    {
        return $this->referenzNumber() ?? '';
    }

    public function syntaxId(): ?string
    {
        return $this->elements->getValue('S001', '0001');
    }

    public function syntaxVersion(): ?string
    {
        return $this->elements->getValue('S001', '0002');
    }

    public function sender(): ?string
    {
        return $this->elements->getValue('S002', '0004');
    }

    public function senderQualifier(): ?string
    {
        return $this->elements->getValue('S002', '0007');
    }

    public function receiver(): ?string
    {
        return $this->elements->getValue('S003', '0010');
    }

    public function receiverQualifier(): ?string
    {
        return $this->elements->getValue('S003', '0007');
    }

    public function creationDateTime(): DateTime
    {
        return DateTime::createFromFormat(
            'ymdHi',
            (string)$this->elements->getValue('S004', '0017') . (string)$this->elements->getValue('S004', '0019')
        );
    }

    public function referenzNumber(): ?string
    {
        return $this->elements->getValue('0020', '0020');
    }

    public function usageType(): ?string
    {
        return $this->elements->getValue('0026', '0026');
    }

    public function testMarker(): ?string
    {
        return $this->elements->getValue('0035', '0035');
    }
}
