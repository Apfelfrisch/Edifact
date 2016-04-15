<?php 

namespace Proengeno\Edifact\Message\Segments;

class Qty extends SegFramework 
{
    protected static $validationBlueprint = [
        'QTY' => ['QTY' => 'M|a|3'],
        'C186' => ['6063' => 'M|an|3', '6060' => 'M|an|35', '6411' => 'M|an|8'],
    ];

    public static function fromAttributes($qualifier, $amount, $unitCode)
    {
        return new static([
            'QTY' => ['QTY' => 'QTY'],
            'C186' => ['6063' => $qualifier, '6060' => $amount, '6411' => $unitCode],
        ]);
    }

    public function qualifier()
    {
        return @$this->elements['C186']['6063'] ?: null;
    }

    public function amount()
    {
        return @$this->elements['C186']['6060'] ?: null;
    }

    public function unitCode()
    {
        return @$this->elements['C186']['6411'] ?: null;
    }
}
