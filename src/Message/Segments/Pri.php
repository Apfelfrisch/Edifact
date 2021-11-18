<?php

namespace Proengeno\Edifact\Message\Segments;

use Proengeno\Edifact\Message\SegmentData;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\DataGroups;
use Proengeno\Edifact\Templates\AbstractSegment;

class Pri extends AbstractSegment
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('PRI', 'PRI', 'M|a|3')
                ->addValue('C509', '5125', 'M|n|3')
                ->addValue('C509', '5118', 'M|n|15')
                ->addValue('C509', '5375', null)
                ->addValue('C509', '5387', null)
                ->addValue('C509', '5284', null)
                ->addValue('C509', '6411', 'D|an|8');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(Delimiter $delimiter, string $qualifier, string $amount, string $unitCode = null): self
    {
        return new self(new SegmentData(
            (new DataGroups)
                ->addValue('PRI', 'PRI', 'PRI')
                ->addValue('C509', '5125', $qualifier)
                ->addValue('C509', '5118', $amount)
                ->addValue('C509', '5375', null)
                ->addValue('C509', '5387', null)
                ->addValue('C509', '5284', null)
                ->addValue('C509', '6411', $unitCode),
            $delimiter
        ));
    }

    public function qualifier(): ?string
    {
        return $this->elements->getValue('C509', '5125');
    }

    public function amount(): ?string
    {
        return $this->elements->getNumericValue('C509', '5118');
    }

    public function unitCode(): ?string
    {
        return $this->elements->getValue('C509', '6411');
    }
}
