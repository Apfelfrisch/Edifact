<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Interfaces\DecimalConverter;
use Apfelfrisch\Edifact\DataGroups;

class Pri extends AbstractSegment implements DecimalConverter
{
    use HasDecimalConverter;

    private static ?DataGroups $blueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new DataGroups)
                ->addValue('PRI', 'PRI', 'M|a|3')
                ->addValue('C509', '5125', 'M|n|3')
                ->addValue('C509', '5118', 'M|n|15')
                ->addValue('C509', '5375', null)
                ->addValue('C509', '5387', null)
                ->addValue('C509', '5284', null)
                ->addValue('C509', '6411', 'D|an|8');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(string $qualifier, string $amount, string $unitCode = null): self
    {
        return new self((new DataGroups)
            ->addValue('PRI', 'PRI', 'PRI')
            ->addValue('C509', '5125', $qualifier)
            ->addValue('C509', '5118', $amount)
            ->addValue('C509', '5375', null)
            ->addValue('C509', '5387', null)
            ->addValue('C509', '5284', null)
            ->addValue('C509', '6411', $unitCode)
        );
    }

    public function qualifier(): ?string
    {
        return $this->elements->getValue('C509', '5125');
    }

    public function amount(): ?string
    {
        return $this->convertToNumeric((string)$this->elements->getValue('C509', '5118'));
    }

    public function unitCode(): ?string
    {
        return $this->elements->getValue('C509', '6411');
    }
}
