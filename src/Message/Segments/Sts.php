<?php

namespace Proengeno\Edifact\Message\Segments;

use Proengeno\Edifact\Message\DataGroups;
use Proengeno\Edifact\Message\SegmentData;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Templates\AbstractSegment;

class Sts extends AbstractSegment
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('STS', 'STS', 'M|a|3')
                ->addValue('C601', '9015', 'M|an|3')
                ->addValue('C555', '4405', 'D|an|3')
                ->addValue('C556', '9013', 'D|an|3')
                ->addValue('C556', '1131', 'D|an|17');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(Delimiter $delimiter, string $category, ?string $reason, ?string $code = null, ?string $status = null): self
    {
        return new self(new SegmentData(
            (new DataGroups)
                ->addValue('STS', 'STS', 'STS')
                ->addValue('C601', '9015', $category)
                ->addValue('C555', '4405', $status)
                ->addValue('C556', '9013', $reason)
                ->addValue('C556', '1131', $code),
            $delimiter
        ));
    }

    public function category(): ?string
    {
        return $this->elements->getValue('C601', '9015');
    }

    public function status(): ?string
    {
        return $this->elements->getValue('C555', '4405');
    }

    public function reason(): ?string
    {
        return $this->elements->getValue('C556', '9013');
    }

    public function code(): ?string
    {
        return $this->elements->getValue('C556', '1131');
    }
}
