<?php 

namespace Proengeno\Edifact\Message\Segments;

class Ide extends Segment 
{
    protected static $validationBlueprint = [
        'IDE' => ['IDE' => 'M|a|3'],
        '7495' => ['7495' => 'M|an|3'],
        'C206' => ['7402' => 'M|an|35'],
    ];

    public static function fromAttributes($qualifier, $idNumber)
    {
        return new static([
            'IDE' => ['IDE' => 'IDE'],
            '7495' => ['7495' => $qualifier],
            'C206' => ['7402' => $idNumber],
        ]);
    }

    public function qualifier()
    {
        return @$this->elements['7495']['7495'] ?: null;
    }

    public function idNumber()
    {
        return @$this->elements['C206']['7402'] ?: null;
    }
}
