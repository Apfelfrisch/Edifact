<?php 

namespace Proengeno\Edifact\Message\Segments;

class Loc extends Segment 
{
    protected static $validationBlueprint = [
        'LOC' => ['LOC' => 'M|a|3'],
        '3227' => ['3227' => 'M|an|3'],
        'C517' => ['3225' => 'M|an|35'],
    ];

    public static function fromAttributes($qualifier, $number)
    {
        return new static([
            'LOC' => ['LOC' => 'LOC'],
            '3227' => ['3227' => $qualifier],
            'C517' => ['3225' => $number],
        ]);
    }

    public function qualifier()
    {
        return @$this->elements['3227']['3227'] ?: null;
    }

    public function number()
    {
        return @$this->elements['C517']['3225'] ?: null;
    }
}
