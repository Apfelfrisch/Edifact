<?php 

namespace Proengeno\Edifact\Message\Segments;

class Com extends Segment 
{
    protected static $validationBlueprint = [
        'COM' => ['COM' => 'M|a|3'],
        'C076' => ['3148' => 'M|an|512', '3155' => 'M|an|3'],
    ];

    public static function fromAttributes($id, $type)
    {
        return new static([
            'COM' => ['COM' => 'COM'],
            'C076' => ['3148' => $id, '3155' => $type],
        ]);
    }

    public function id()
    {
        return @$this->elements['C076']['3148'] ?: null;
    }

    public function type()
    {
        return @$this->elements['C076']['3155'] ?: null;
    }
}
