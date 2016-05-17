<?php 

namespace Proengeno\Edifact\Message\Segments;

class Doc extends Segment 
{
    protected static $validationBlueprint = [
        'DOC' => ['DOC' => 'M|a|3'],
        'C002' => ['1001' => 'M|an|3'],
        'C503' => ['1004' => 'M|an|35'],
    ];

    public static function fromAttributes($code, $number)
    {
        return new static([
            'DOC' => ['DOC' => 'DOC'],
            'C002' => ['1001' => $code],
            'C503' => ['1004' => $number],
        ]);
    }

    public function code()
    {
        return @$this->elements['C002']['1001'] ?: null;
    }

    public function number()
    {
        return @$this->elements['C503']['1004'] ?: null;
    }
}
