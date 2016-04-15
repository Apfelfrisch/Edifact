<?php 

namespace Proengeno\Edifact\Message\Segments;

class Bgm extends SegFramework 
{
    protected static $validationBlueprint = [
        'BGM' => ['BGM' => 'M|a|3'],
        'C002' => ['1001' => 'M|an|3'],
        'C106' => ['1004' => 'M|an|35'],
        '1225' => ['1225' => 'O|an|3'],
    ];

    public static function fromAttributes($docCode, $docNumber, $messageCode = null)
    {
        return new static([
            'BGM' => ['BGM' => 'BGM'],
            'C002' => ['1001' => $docCode],
            'C106' => ['1004' => $docNumber],
            '1225' => ['1225' => $messageCode],
        ]);
    }

    public function docCode()
    {
        return @$this->elements['C002']['1001'] ?: null;
    }

    public function docNumber()
    {
        return @$this->elements['C106']['1004'] ?: null;
    }

    public function messageCode()
    {
        return @$this->elements['1225']['1225'] ?: null;
    }
}
