<?php

namespace Proengeno\Edifact\Segments;

use Proengeno\Edifact\DataGroups;
use Proengeno\Edifact\Delimiter;

class Una extends AbstractSegment
{
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('UNA', 'UNA', 'M|a|3')
                ->addValue('UNA', 'data', 'M|an|1')
                ->addValue('UNA', 'dataGroup', 'M|an|1')
                ->addValue('UNA', 'decimal', 'M|an|1')
                ->addValue('UNA', 'terminator', 'M|an|1')
                ->addValue('UNA', 'empty', 'M|an|1')
                ->addValue('UNA', 'segment', 'O|an|1');
        }

        return self::$validationBlueprint;
    }

    public static function fromAttributes(
        string $data = ':',
        string $dataGroup = '+',
        string $decimal = '.',
        string $terminator = '?',
        string $empty = ' ',
        string $segment = '\''
    ): self
    {
        return new self((new DataGroups)
            ->addValue('UNA', 'UNA', 'UNA')
            ->addValue('UNA', 'data', $data)
            ->addValue('UNA', 'dataGroup', $dataGroup)
            ->addValue('UNA', 'decimal', $decimal)
            ->addValue('UNA', 'terminator', $terminator)
            ->addValue('UNA', 'empty', $empty)
            ->addValue('UNA', 'segment', $segment)
        );
    }

    public function data(): string
    {
        return (string)$this->elements->getValue('UNA', 'data');
    }

    public function dataGroup(): string
    {
        return (string)$this->elements->getValue('UNA', 'dataGroup');
    }

    public function decimal(): string
    {
        return (string)$this->elements->getValue('UNA', 'decimal');
    }

    public function terminator(): string
    {
        return (string)$this->elements->getValue('UNA', 'terminator');
    }

    public function emptyChar(): string
    {
        return (string)$this->elements->getValue('UNA', 'empty');
    }

    public function segment(): ?string
    {
        return $this->elements->getValue('UNA', 'segment');
    }

    public function toString(Delimiter $delimiter): string
    {
        return $this->name() . $this->data() . $this->dataGroup() . $this->decimal() . $this->terminator() . $this->emptyChar();
    }

    protected static function mapToBlueprint(Delimiter $delimiter, string $segLine): DataGroups
    {
        $inputElement = ['UNA'] + str_split(substr($segLine, 2));
        $dataGroups = new DataGroups;

        $i = 0;
        foreach (array_keys(self::blueprint()->getDataGroup('UNA')) as $BpDataKey) {
            $dataGroups->addValue('UNA', $BpDataKey, $inputElement[$i] ?? null);
            $i++;
        }

        return $dataGroups;
    }
}
