<?php

namespace Proengeno\Edifact\Message\Segments;

use Proengeno\Edifact\Message\DataGroups;
use Proengeno\Edifact\Message\SegmentData;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Templates\AbstractSegment;

class Moa extends AbstractSegment
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('MOA', 'MOA', 'M|a|3')
                ->addValue('C516', '5025', 'M|an|3')
                ->addValue('C516', '5004', 'M|n|35');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(Delimiter $delimiter, string $qualifier, float $amount): self
    {
        return new self(new SegmentData(
            (new DataGroups)
                ->addValue('MOA', 'MOA', 'MOA')
                ->addValue('C516', '5025', $qualifier)
                ->addValue('C516', '5004', number_format($amount, 2, $delimiter->getDecimal(), '')),
            $delimiter
        ));
    }

    public function qualifier(): ?string
    {
        return $this->elements->getValue('C516', '5025');
    }

    public function amount(): ?string
    {
        return $this->elements->getNumericValue('C516', '5004');
    }
}
