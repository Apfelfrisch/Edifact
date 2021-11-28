<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Elements;
use Apfelfrisch\Edifact\Delimiter;
use Apfelfrisch\Edifact\SeglineParser;

class Una extends AbstractSegment
{
    private static ?Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new Elements)
                ->addValue('UNA', 'UNA', 'M|a|3')
                ->addValue('UNA', 'componentSeparator', 'M|an|1')
                ->addValue('UNA', 'elementSeparator', 'M|an|1')
                ->addValue('UNA', 'decimalPoint', 'M|an|1')
                ->addValue('UNA', 'escapeCharacter', 'M|an|1')
                ->addValue('UNA', 'spaceCharacter', 'M|an|1')
                ->addValue('UNA', 'segmentTerminator', 'O|an|1');
        }

        return self::$blueprint;
    }

    public static function fromSegLine(SeglineParser $parser, string $segLine): static
    {
        $segment = new static(static::mapToBlueprint($parser->getDelimiter(), $segLine));
        $segment->setDelimiter($parser->getDelimiter());

        return $segment;
    }

    public static function fromAttributes(
        string $componentSeparator = ':',
        string $elementSeparator = '+',
        string $decimalPoint = '.',
        string $escapeCharacter = '?',
        string $spaceCharacter = ' ',
        string $segmentTerminator = '\''
    ): self
    {
        return new self((new Elements)
            ->addValue('UNA', 'UNA', 'UNA')
            ->addValue('UNA', 'componentSeparator', $componentSeparator)
            ->addValue('UNA', 'elementSeparator', $elementSeparator)
            ->addValue('UNA', 'decimalPoint', $decimalPoint)
            ->addValue('UNA', 'escapeCharacter', $escapeCharacter)
            ->addValue('UNA', 'spaceCharacter', $spaceCharacter)
            ->addValue('UNA', 'segmentTerminator', $segmentTerminator)
        );
    }

    public function componentSeparator(): string
    {
        return (string)$this->elements->getValue('UNA', 'componentSeparator');
    }

    public function elementSeparator(): string
    {
        return (string)$this->elements->getValue('UNA', 'elementSeparator');
    }

    public function decimalPoint(): string
    {
        return (string)$this->elements->getValue('UNA', 'decimalPoint');
    }

    public function escapeCharacter(): string
    {
        return (string)$this->elements->getValue('UNA', 'escapeCharacter');
    }

    public function spaceCharacter(): string
    {
        return (string)$this->elements->getValue('UNA', 'spaceCharacter');
    }

    public function segmentTerminator(): ?string
    {
        return $this->elements->getValue('UNA', 'segmentTerminator');
    }

    public function toString(): string
    {
        return $this->name() . $this->componentSeparator() . $this->elementSeparator() . $this->decimalPoint() . $this->escapeCharacter() . $this->spaceCharacter();
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
