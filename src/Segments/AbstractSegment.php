<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\DataGroups;
use Apfelfrisch\Edifact\Delimiter;
use Apfelfrisch\Edifact\Interfaces\SegInterface;
use Apfelfrisch\Edifact\Validation\SegmentValidator;
use Apfelfrisch\Edifact\Interfaces\SegValidatorInterface;

abstract class AbstractSegment implements SegInterface
{
    protected ?Delimiter $delimiter = null;

    protected DataGroups $elements;

    protected SegValidatorInterface $validator;

    final protected function __construct(DataGroups $elements)
    {
        $this->elements = $elements;
        $this->validator = new SegmentValidator;
    }

    abstract public static function blueprint(): DataGroups;

    public static function fromSegLine(Delimiter $delimiter, string $segLine): static
    {
        $segment = new static(static::mapToBlueprint($delimiter, $segLine));
        $segment->setDelimiter($delimiter);

        return $segment;
    }

    public function setDelimiter(Delimiter $delimiter): void
    {
        $this->delimiter = $delimiter;
    }

    public function getValueFromPosition(int $dataGroupPosition, int $valuePosition): ?string
    {
        return $this->elements->getValueFromPosition($dataGroupPosition, $valuePosition);
    }

    public function getValue(string $dataGroupKey, string $valueKey): ?string
    {
        return $this->elements->getValue($dataGroupKey, $valueKey);
    }

    public function replaceDecimalPoint(?string $value): ?string
    {
        if ($this->getDelimiter()->getDecimalPoint() !== '.' && $value !== null) {
            return str_replace($this->getDelimiter()->getDecimalPoint(), '.', $value);
        }

        return $value;
    }

    public function replaceSpaceCharacter(?string $value): ?string
    {
        if ($this->getDelimiter()->getSpaceCharacter() !== ' ' && $value !== null) {
            return str_replace($this->getDelimiter()->getSpaceCharacter(), '.', $value);
        }

        return $value;
    }

    public function name(): string
    {
        return $this->elements->getName();
    }

    public function validate(): void
    {
        $this->validator->validate(static::blueprint(), $this->elements);
    }

    /**
     * @psalm-return array<string, array<string, string|null>>
     */
    public function toArray(): array
    {
        return $this->elements->toArray();
    }

    public function toString(): string
    {
        $string = '';

        foreach($this->elements->toArray() as $dataGroup) {
            foreach ($dataGroup as $value) {
                $string .= $value === null
                    ? $this->getDelimiter()->getComponentSeparator()
                    : $this->getDelimiter()->terminate($value) . $this->getDelimiter()->getComponentSeparator();
            }

            $string = $this->trimEmpty(
                $string, $this->getDelimiter()->getComponentSeparator(), $this->getDelimiter()->getEscapeCharacter()
            ) . $this->getDelimiter()->getElementSeparator();
        }

        return $this->trimEmpty($string, $this->getDelimiter()->getElementSeparator(), $this->getDelimiter()->getEscapeCharacter());
    }

    private function getDelimiter(): Delimiter
    {
        return $this->delimiter ??= Delimiter::getDefault();
    }

    private function trimEmpty(string $string, string $dataGroupSeperator, string $terminator): string
    {
        while(true) {
            if ($dataGroupSeperator !== $string[-1] ?? null) {
                break;
            }

            if ($terminator === $string[-2] ?? null) {
                break;
            }

            $string = substr($string, 0, -1);
        }

        return $string;
    }

    protected static function mapToBlueprint(Delimiter $delimiter, string $segLine): DataGroups
    {
        $i = 0;
        $dataGroups = new DataGroups;
        $dataArray = $delimiter->explodeDataGroups($segLine);
        foreach (static::blueprint()->toArray() as $BpDataKey => $BPdataGroups) {
            $inputElement = [];
            if (isset($dataArray[$i])) {
                $inputElement = $delimiter->explodeElements($dataArray[$i]);
            }

            $j = 0;
            foreach (array_keys($BPdataGroups) as $key) {
                $dataGroups->addValue($BpDataKey, $key, isset($inputElement[$j]) ? $inputElement[$j] : null);
                $j++;
            }
            $i++;
        }

        return $dataGroups;
    }
}
