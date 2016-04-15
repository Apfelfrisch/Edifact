<?php 

namespace Proengeno\Edifact\Message\Segments;

class Cav extends SegFramework 
{
    protected static $validationBlueprint = [
        'CAV' => ['CAV' => 'M|a|3'],
        'C889' => [ '7111' => 'M|an|3', '1131' => null, '3055' => 'M|an|3', '7110' => 'M|an|35'],
    ];

    public static function fromAttributes($code, $responsCode, $value)
    {
        return new static([
            'CAV' => ['CAV' => 'CAV'],
            'C889' => [ '7111' => $code, '1131' => null, '3055' => $responsCode, '7110' => $value],
        ]);
    }

    public function code()
    {
        return @$this->elements['C889']['7111'] ?: null;
    }

    public function responsCode()
    {
        return @$this->elements['C889']['3055'] ?: null;
    }

    public function value()
    {
        return @$this->elements['C889']['7110'] ?: null;
    }
}
