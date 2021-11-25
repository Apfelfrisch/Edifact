<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Interfaces\DecimalConverter;
use Apfelfrisch\Edifact\DataGroups;

class Moa extends AbstractSegment implements DecimalConverter
{
    use HasDecimalConverter;

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

    /**
     * @todo $amount to string, remove $decimalSeperator: Its not the buisness of this class
     */
    public static function fromAttributes(string $qualifier, float $amount, string $decimalSeperator = '.'): self
    {
        return new self((new DataGroups)
            ->addValue('MOA', 'MOA', 'MOA')
            ->addValue('C516', '5025', $qualifier)
            ->addValue('C516', '5004', number_format($amount, 2, $decimalSeperator, ''))
        );
    }

    public function qualifier(): ?string
    {
        return $this->elements->getValue('C516', '5025');
    }

    public function amount(): ?string
    {
        return $this->convertToNumeric((string)$this->elements->getValue('C516', '5004'));
    }
}
