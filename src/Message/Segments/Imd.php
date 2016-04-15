<?php 

namespace Proengeno\Edifact\Message\Segments;

class Imd extends SegFramework 
{
    protected static $validationBlueprint = [
        'IMD' => ['IMD' => 'M|a|3'],
        '7077' => ['7077' => null],
        'C272' => ['7081' => 'M|an|3'],
        'C273' => ['7009' => 'O|an|17']
    ];

    public static function fromAttributes($code, $qualifier = null)
    {
        return new static([
            'IMD' => ['IMD' => 'IMD'],
            '7077' => ['7077' => null],
            'C272' => ['7081' => $code],
            'C273' => ['7009' => $qualifier],
        ]);
    }

    public function code()
    {
        return @$this->elements['C272']['7081'] ?: null;
    }

    public function qualifier()
    {
        return @$this->elements['C273']['7009'] ?: null;
    }
}
