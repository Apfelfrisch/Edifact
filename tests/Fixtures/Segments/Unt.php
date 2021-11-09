<?php

namespace Proengeno\Edifact\Test\Fixtures\Segments;

use Proengeno\Edifact\Message\DataGroupCollection;
use Proengeno\Edifact\Templates\AbstractSegment;

class Unt extends AbstractSegment
{
    protected static $validationBlueprint = [
        'UNT' => ['UNT' => 'M|a|3'],
        '0074' => ['0074' => 'M|n|6'],
        '0062' => ['0062' => 'M|an|15'],
    ];

    public static function fromAttributes($segCount, $referenz)
    {
        return new static(
            (new DataGroupCollection(static::getBuildDelimiter()))
                ->addValue('UNT', 'UNT', 'UNT')
                ->addValue('0074', '0074', $segCount)
                ->addValue('0062', '0062', $referenz)
        );
    }

    public function segCount()
    {
        return @$this->elements['0074']['0074'] ?: null;
    }

    public function referenz()
    {
        return @$this->elements['0062']['0062'] ?: null;
    }
}
