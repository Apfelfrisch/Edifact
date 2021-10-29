<?php

namespace Proengeno\Edifact\Test\Fixtures\Segments;

use Proengeno\Edifact\Interfaces\SegInterface;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Templates\AbstractSegment;

class Una extends AbstractSegment
{
    protected static $validationBlueprint = [
        'UNA' => ['Una' => 'M|a|3', 'data' => 'M|an|1', 'dataGroup' => 'M|an|1', 'decimal' => 'M|an|1', 'terminator' => 'M|an|1', 'empty' => 'M|an|1'],
    ];

    public static function fromSegline(string $segLine, ?Delimiter $delimiter = null): SegInterface
    {
        $elements = [];
        $inputElement = ['UNA'] + str_split(substr($segLine, 2));
        $i = 0;
        foreach (self::$validationBlueprint as $BpDataKey => $BPdataGroups) {
            if (isset($inputElement)) {
                $elements[$BpDataKey] = array_combine(array_keys($BPdataGroups), $inputElement);
            }
            $i++;
        }

        return new self($elements);
    }

    public static function fromAttributes($data = ':', $dataGroup = '+', $decimal = '.', $terminator = '?', $empty = ' ')
    {
        return new self([
            'UNA' => ['UNA' => 'UNA', 'data' => $data, 'dataGroup' => $dataGroup, 'decimal' => $decimal, 'terminator' => $terminator, 'empty' => $empty],
        ]);
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

    public function toString(?Delimiter $delimiter = null): string
    {
        $delimiter ??= new Delimiter;

        return $this->name()
            . $this->data()
            . $this->dataGroup()
            . $this->decimal()
            . $this->terminator()
            . $this->emptyChar()
            . $delimiter->getSegment();
    }
}
