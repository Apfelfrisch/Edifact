<?php 

namespace Proengeno\Edifact\Test\Fixtures\Segments;

use Proengeno\Edifact\Templates\AbstractSegment;

class Unz extends AbstractSegment 
{
    protected static $validationBlueprint = [
        'UNZ' => ['UNZ' => 'M|an|3'],
        '0062' => ['0062' => 'M|n|6'],
        'S009' => ['0065' => 'M|an|35'],
    ];

    public static function fromAttributes($counter, $referenz)
    {
        $instance = new static([
            'UNZ' => ['UNZ' => 'UNZ'],
            '0062' => ['0062' => $counter],
            'S009' => ['0065' => $referenz],
        ]);

        return $instance;
    }

    public function counter()
    {
        return @$this->elements['0062']['0062'] ?: null;
    }

    public function referenz()
    {
        return @$this->elements['S009']['0065'] ?: null;
    }
}
