<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Elements;
use Apfelfrisch\Edifact\Delimiter;

class Una extends AbstractSegment
{
    private static ?Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new Elements)
                ->addValue('UNA', 'UNA', 'M|a|3')
                ->addValue('UNA', 'data', 'M|an|1')
                ->addValue('UNA', 'element', 'M|an|1')
                ->addValue('UNA', 'decimal', 'M|an|1')
                ->addValue('UNA', 'terminator', 'M|an|1')
                ->addValue('UNA', 'empty', 'M|an|1')
                ->addValue('UNA', 'segment', 'O|an|1');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(
        string $data = ':',
        string $element = '+',
        string $decimal = '.',
        string $terminator = '?',
        string $empty = ' ',
        string $segment = '\''
    ): self
    {
        return new self((new Elements)
            ->addValue('UNA', 'UNA', 'UNA')
            ->addValue('UNA', 'data', $data)
            ->addValue('UNA', 'element', $element)
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

    public function element(): string
    {
        return (string)$this->elements->getValue('UNA', 'element');
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

    public function toString(): string
    {
        return $this->name() . $this->data() . $this->element() . $this->decimal() . $this->terminator() . $this->emptyChar();
    }

    protected static function mapToBlueprint(Delimiter $delimiter, string $segLine): Elements
    {
        $inputElement = ['UNA'] + str_split(substr($segLine, 2));
        $elements = new Elements;

        $i = 0;
        foreach (array_keys(self::blueprint()->getElement('UNA')) as $BpDataKey) {
            $elements->addValue('UNA', $BpDataKey, $inputElement[$i] ?? null);
            $i++;
        }

        return $elements;
    }
}
