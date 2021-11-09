<?php

namespace Proengeno\Edifact\Message\Segments;

use Proengeno\Edifact\Message\DataGroups;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\SegmentData;
use Proengeno\Edifact\Templates\AbstractSegment;

class Qty extends AbstractSegment
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('QTY', 'QTY', 'M|a|3')
                ->addValue('C186', '6063', 'M|an|3')
                ->addValue('C186', '6060', 'M|an|35')
                ->addValue('C186', '6411', 'D|an|8');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(Delimiter $delimiter, string $qualifier, string $amount, ?string $unitCode = null): self
    {
        return new self(new SegmentData(
            (new DataGroups)
                ->addValue('QTY', 'QTY', 'QTY')
                ->addValue('C186', '6063', $qualifier)
                ->addValue('C186', '6060', $amount)
                ->addValue('C186', '6411', $unitCode),
            $delimiter
        ));
    }

    public function qualifier(): ?string
    {
        return $this->elements->getValue('C186', '6063');
    }

    public function amount(): ?string
    {
        return $this->elements->getNumericValue('C186', '6060');
    }

    public function unitCode(): ?string
    {
        return $this->elements->getValue('C186', '6411');
    }
}
