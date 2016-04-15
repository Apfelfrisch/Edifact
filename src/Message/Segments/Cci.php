<?php 

namespace Proengeno\Edifact\Message\Segments;

class Cci extends SegFramework 
{
    protected static $validationBlueprint = [
        'CCI' => ['CCI' => 'M|a|3'],
        '7059' => ['7059' => 'M|an|3'],
        'C502' => ['6313' => null],
        'C240' => ['7037' => 'M|an|17'],
    ];

    public static function fromAttributes($type, $code)
    {
        return new static([
            'CCI' => ['CCI' => 'CCI'],
            '7059' => ['7059' => $type],
            'C502' => ['6313' => null],
            'C240' => ['7037' => $code],
        ]);
    }

    public function type()
    {
        return @$this->elements['7059']['7059'] ?: null;
    }

    public function code()
    {
        return @$this->elements['C240']['7037'] ?: null;
    }
}
