<?php 

namespace Proengeno\Edifact\Message\Segments;

class Rff extends Segment 
{
    protected static $validationBlueprint = [
        'RFF' => ['RFF' => 'M|a|3'],
        'C506' => ['1153' => 'M|an|3', '1154' => 'M|an|70'],
    ];

    public static function fromAttributes($code, $referenz)
    {
        return new static([
            'RFF' => ['RFF' => 'RFF'],
            'C506' => ['1153' => $code, '1154' => $referenz],
        ]);
    }

    public function code()
    {
        return @$this->elements['C506']['1153'] ?: null;
    }

    public function referenz()
    {
        return @$this->elements['C506']['1154'] ?: null;
    }
}
