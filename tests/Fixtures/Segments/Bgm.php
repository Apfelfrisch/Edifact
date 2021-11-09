<?php

namespace Proengeno\Edifact\Test\Fixtures\Segments;

use Proengeno\Edifact\Message\DataGroupCollection;
use Proengeno\Edifact\Templates\AbstractSegment;

class Bgm extends AbstractSegment
{
    protected static $validationBlueprint = [
        'BGM' => ['BGM' => 'M|a|3'],
        'C002' => ['1001' => 'M|an|3'],
        'C106' => ['1004' => 'M|an|35'],
        '1225' => ['1225' => 'O|an|3'],
    ];

    public static function fromAttributes($docCode, $docNumber, $messageCode = null)
    {
        return new static(
            (new DataGroupCollection(static::getBuildDelimiter()))
                ->addValue('BGM', 'BGM', 'BGM')
                ->addValue('C002', '1001', $docCode)
                ->addValue('C106', '1004', $docNumber)
                ->addValue('1225', '1225', $messageCode)
        );
    }

    public function docCode()
    {
        return $this->elements->getValue('C002', '1001');
    }
}
