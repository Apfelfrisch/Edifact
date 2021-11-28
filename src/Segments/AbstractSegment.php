<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Elements;
use Apfelfrisch\Edifact\Delimiter;
use Apfelfrisch\Edifact\Interfaces\SegInterface;
use Apfelfrisch\Edifact\Validation\SegmentValidator;
use Apfelfrisch\Edifact\Interfaces\SegValidatorInterface;
use Apfelfrisch\Edifact\SeglineParser;
use Apfelfrisch\Edifact\StringFormatter;

abstract class AbstractSegment implements SegInterface
{
    protected ?Delimiter $delimiter = null;

    protected Elements $elements;

    protected SegValidatorInterface $validator;

    final protected function __construct(Elements $elements)
    {
        $this->elements = $elements;
        $this->validator = new SegmentValidator;
    }

    abstract public static function blueprint(): Elements;

    public static function fromSegLine(SeglineParser $parser, string $segLine): static
    {
        $segment = new static($parser->parseToBlueprint($segLine, static::blueprint()));
        $segment->setDelimiter($parser->getDelimiter());

        return $segment;
    }

    public function setDelimiter(Delimiter $delimiter): void
    {
        $this->delimiter = $delimiter;
    }

    public function getValueFromPosition(int $elementPosition, int $valuePosition): ?string
    {
        return $this->elements->getValueFromPosition($elementPosition, $valuePosition);
    }

    public function getValue(string $elementKey, string $componentKey): ?string
    {
        return $this->elements->getValue($elementKey, $componentKey);
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
        return substr((new StringFormatter($this->getDelimiter()))->format($this), 0, -1);
    }

    private function getDelimiter(): Delimiter
    {
        return $this->delimiter ??= Delimiter::getDefault();
    }
}
