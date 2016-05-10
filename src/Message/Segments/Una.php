<?php 

namespace Proengeno\Edifact\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;

class Una extends Segment 
{
    protected static $validationBlueprint = [
        'UNA' => ['Una' => 'M|a|3', 'data' => 'M|an|1', 'dataGroup' => 'M|an|1', 'decimal' => 'M|an|1', 'terminator' => 'M|an|1', 'empty' => 'M|an|1'],
    ];

    public static function fromAttributes($data = ':', $dataGroup = '+', $decimal = '.', $terminator = '?', $empty = ' ')
    {
        static::setDelimiter(new Delimiter($data, $dataGroup, $decimal, $terminator, $empty));
        return new static([
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

    public function __toString()
    {
        return $this->segLine = implode('', $this->elements['UNA']) . "'";
    }

    protected static function mapToBlueprint($segLine)
    {
        $inputElement = ['UNA'] + str_split(substr($segLine, 2));
        $i = 0;
        foreach (static::$validationBlueprint as $BpDataKey => $BPdataGroups) {
            if (isset($inputElement)) {
                $elements[$BpDataKey] = array_combine(array_keys($BPdataGroups), $inputElement);
            }
            $i++;
        }

        return @$elements ?: [];
    }
}
