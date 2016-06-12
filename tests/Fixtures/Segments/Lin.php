<?php 

namespace Proengeno\Edifact\Test\Fixtures\Segments;

use Proengeno\Edifact\Templates\AbstractSegment;

class Lin extends AbstractSegment 
{
    protected static $validationBlueprint = [
        'LIN' => ['LIN' => 'M|a|3'],
        '1082' => ['1082' => 'M|n|6'],
        '1229' => ['1229' => null],
        'C212' => ['7140' => 'D|an|35', '7143' => 'D|an|3'],
    ];

    public static function fromAttributes($number, $articleNumber, $articleCode)
    {
        return new static([
            'LIN' => ['LIN' => 'LIN'],
            '1082' => ['1082' => $number],
            '1229' => ['1229' => null],
            'C212' => ['7140' => $articleNumber, '7143' => $articleCode],
        ]);
    }

    public function number()
    {
        return @$this->elements['1082']['1082'] ?: null;
    }

    public function articleNumber()
    {
        return @$this->elements['C212']['7140'] ?: null;
    }

    public function articleCode()
    {
        return @$this->elements['C212']['7143'] ?: null;
    }
}
