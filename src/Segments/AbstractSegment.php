<?php

namespace Proengeno\Edifact\Segments;

use Proengeno\Edifact\Exceptions\SegValidationException;
use Proengeno\Edifact\Interfaces\DecimalConverter;
use Proengeno\Edifact\DataGroups;
use Proengeno\Edifact\SegmentData;
use Proengeno\Edifact\Delimiter;
use Proengeno\Edifact\Interfaces\SegInterface;
use Proengeno\Edifact\Validation\SegmentValidator;
use Proengeno\Edifact\Interfaces\SegValidatorInterface;

abstract class AbstractSegment implements SegInterface
{
    protected SegmentData $elements;

    protected array $cache = [];

    protected SegValidatorInterface $validator;

    final protected function __construct(SegmentData $elements)
    {
        $this->elements = $elements;
        $this->validator = new SegmentValidator;
    }

    abstract public static function blueprint(): DataGroups;

    public static function fromSegLine(Delimiter $delimiter, string $segLine): static
    {
        $segment = new static(static::mapToBlueprint($delimiter, $segLine));

        if (is_subclass_of($segment, DecimalConverter::class)) {
            /** @var DecimalConverter $segment */
            $segment->setDecimalSeparator($delimiter->getDecimal());
        }

        return $segment;
    }

    public function getValue(int $dataGroupKey, int $valueKey): ?string
    {
        return $this->elements->getValueFromPosition($dataGroupKey, $valueKey);
    }

    public function getValidator(): SegValidatorInterface
    {
        return $this->validator;
    }

    public function name(): string
    {
        if (null === $name = $this->getValue(0,0)) {
            throw SegValidationException::forKey('name', "Segment is empty");
        }

        return $name;
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

    public function toString(Delimiter $delimiter): string
    {
        return $this->elements->toString($delimiter);
    }

    protected static function mapToBlueprint(Delimiter $delimiter, string $segLine): SegmentData
    {
        $i = 0;
        $dataCollection = new DataGroups;
        $inputDataGroups = $delimiter->explodeDataGroups($segLine);
        foreach (static::blueprint()->toArray() as $BpDataKey => $BPdataGroups) {
            $inputElement = [];
            if (isset($inputDataGroups[$i])) {
                $inputElement = $delimiter->explodeElements($inputDataGroups[$i]);
            }

            $j = 0;
            foreach (array_keys($BPdataGroups) as $key) {
                $dataCollection->addValue($BpDataKey, $key, isset($inputElement[$j]) ? $inputElement[$j] : null);
                $j++;
            }
            $i++;
        }

        return new SegmentData($dataCollection);
    }
}
