<?php

namespace Proengeno\Edifact\Test\Fixtures\Segments;

use Proengeno\Edifact\Message\DataGroupCollection;
use Proengeno\Edifact\Templates\AbstractSegment;

class Una extends AbstractSegment
{
    protected static $validationBlueprint = [
        'UNA' => ['Una' => 'M|a|3', 'data' => 'M|an|1', 'dataGroup' => 'M|an|1', 'decimal' => 'M|an|1', 'terminator' => 'M|an|1', 'empty' => 'M|an|1'],
    ];

    public static function fromAttributes($data = ':', $dataGroup = '+', $decimal = '.', $terminator = '?', $empty = ' ')
    {
        return new static(
            (new DataGroupCollection(static::getBuildDelimiter()))
                ->addValue('UNA', 'UNA', 'UNA')
                ->addValue('UNA', 'data', $data)
                ->addValue('UNA', 'dataGroup', $dataGroup)
                ->addValue('UNA', 'decimal', $decimal)
                ->addValue('UNA', 'terminator', $terminator)
                ->addValue('UNA', 'empty', $empty)
        );
    }

    public function data()
    {
        return @$this->elements['UNA']['data'] ?: null;
    }

    public function dataGroup()
    {
        return @$this->elements['UNA']['dataGroup'] ?: null;
    }

    public function decimal()
    {
        return @$this->elements['UNA']['decimal'] ?: null;
    }

    public function terminator()
    {
        return @$this->elements['UNA']['terminator'] ?: null;
    }

    public function emptyChar()
    {
        return @$this->elements['UNA']['empty'] ?: null;
    }

    public function toString(): string
    {
        return $this->elements->getValue('UNA', 'UNA')
            . $this->elements->getValue('UNA', 'data')
            . $this->elements->getValue('UNA', 'dataGroup')
            . $this->elements->getValue('UNA', 'decimal')
            . $this->elements->getValue('UNA', 'terminator')
            . $this->elements->getValue('UNA', 'empty');
    }

    protected static function mapToBlueprint(string $segLine): DataGroupCollection
    {
        $inputElement = ['UNA'] + str_split(substr($segLine, 2));
        $dataCollection = new DataGroupCollection(static::getBuildDelimiter());

        $i = 0;
        foreach (static::$validationBlueprint['UNA'] as $BpDataKey => $BPdataGroups) {
            $dataCollection->addValue('UNA', $BpDataKey, $inputElement[$i] ?? null);
            $i++;
        }

        return $dataCollection;
    }
}
