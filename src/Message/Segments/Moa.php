<?php 

namespace Proengeno\Edifact\Message\Segments;

class Moa extends Segment 
{
    protected static $validationBlueprint = [
        'MOA' => ['MOA' => 'M|a|3'],
        'C516' => ['5025' => 'M|an|3', '5004' => 'M|n|35'],
    ];

    public static function fromAttributes($qualifier, $amount)
    {
        return new static([
            'MOA' => ['MOA' => 'MOA'],
            'C516' => ['5025' => $qualifier, '5004' => $amount]
        ]);
    }

    public function qualifier()
    {
        return @$this->elements['C516']['5025'] ?: null;
    }

    public function amount()
    {
        return @$this->elements['C516']['5004'] ?: null;
    }
}
