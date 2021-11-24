<?php

namespace Proengeno\Edifact\Segments;

use Proengeno\Edifact\Exceptions\SegValidationException;
use Proengeno\Edifact\Interfaces\DecimalConverter;
use Proengeno\Edifact\DataGroups;
use Proengeno\Edifact\Delimiter;
use Proengeno\Edifact\Interfaces\SegInterface;
use Proengeno\Edifact\Validation\SegmentValidator;
use Proengeno\Edifact\Interfaces\SegValidatorInterface;

abstract class AbstractSegment implements SegInterface
{
    protected DataGroups $elements;

    protected array $cache = [];

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
        $string = '';

        foreach($this->elements->toArray() as $dataGroup) {
            foreach ($dataGroup as $value) {
                $string .= $value === null
                    ? $delimiter->getData()
                    : $delimiter->terminate($value) . $delimiter->getData();
            }

            $string = $this->trimEmpty(
                $string, $delimiter->getData(), $delimiter->getTerminator()
            ) . $delimiter->getDataGroup();
        }

        return $this->trimEmpty($string, $delimiter->getDataGroup(), $delimiter->getTerminator());
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
