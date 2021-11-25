<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\DataGroups;

class Uci extends AbstractSegment
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('UCI', 'UCI', 'M|an|3')
                ->addValue('0020', '0020', 'M|an|14')
                ->addValue('S002', '0004', 'M|an|35')
                ->addValue('S002', '0007', 'M|an|3')
                ->addValue('S003', '0010', 'M|an|35')
                ->addValue('S003', '0007', 'M|an|3')
                ->addValue('0083', '0083', 'M|n|1')
                ->addValue('0085', '0085', 'O|n|2')
                ->addValue('0013', '0013', 'O|a|3')
                ->addValue('S011', '0098', 'O|n|3')
                ->addValue('S011', '0104', 'O|n|3');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(
        string $unbRef,
        string $sender,
        string $senderCode,
        string $receiver,
        string $receiverCode,
        string $statusCode,
        ?string $errorCode = null,
        ?string $serviceSegement = null,
        ?string $segmentPosition = null,
        ?string $elementPosition = null
    ): self
    {
        return new self((new DataGroups)
            ->addValue('UCI', 'UCI', 'UCI')
            ->addValue('0020', '0020', $unbRef)
            ->addValue('S002', '0004', $sender)
            ->addValue('S002', '0007', $senderCode)
            ->addValue('S003', '0010', $receiver)
            ->addValue('S003', '0007', $receiverCode)
            ->addValue('0083', '0083', $statusCode)
            ->addValue('0085', '0085', $errorCode)
            ->addValue('0013', '0013', $serviceSegement)
            ->addValue('S011', '0098', $segmentPosition)
            ->addValue('S011', '0104', $elementPosition)
        );
    }

    public function unbRef(): ?string
    {
        return $this->elements->getValue('0020', '0020');
    }

    public function sender(): ?string
    {
        return $this->elements->getValue('S002', '0004');
    }

    public function senderCode(): ?string
    {
        return $this->elements->getValue('S002', '0007');
    }

    public function receiver(): ?string
    {
        return $this->elements->getValue('S003', '0010');
    }

    public function receiverCode(): ?string
    {
        return $this->elements->getValue('S003', '0007');
    }

    public function statusCode(): ?string
    {
        return $this->elements->getValue('0083', '0083');
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

    public function elementPosition(): ?string
    {
        return $this->elements->getValue('S011', '0104');
    }
}
