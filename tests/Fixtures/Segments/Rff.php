<?php

namespace Proengeno\Edifact\Test\Fixtures\Segments;

use Proengeno\Edifact\Message\DataGroupCollection;
use Proengeno\Edifact\Templates\AbstractSegment;

class Rff extends AbstractSegment
{
    protected static $validationBlueprint = [
        'RFF' => ['RFF' => 'M|a|3'],
        'C506' => ['1153' => 'M|an|3', '1154' => 'M|an|70'],
    ];

    public static function fromAttributes($code, $referenz)
    {
        return new static(
            (new DataGroupCollection(static::getBuildDelimiter()))
                ->addValue('RFF', 'RFF', 'RFF')
                ->addValue('C506', '1153', $code)
                ->addValue('C506', '1154', $referenz)
        );
    }

    public function code()
    {
        return $this->elements->getValue('C506', '1153');
    }

    public function referenz()
    {
        return @$this->elements['C506']['1154'] ?: null;
    }
}
