<?php

namespace Proengeno\Edifact\Templates;

use Proengeno\Edifact\Exceptions\SegValidationException;
use Proengeno\Edifact\Message\DataGroups;
use Proengeno\Edifact\Message\SegmentData;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Interfaces\SegInterface;
use Proengeno\Edifact\Validation\SegmentValidator;
use Proengeno\Edifact\Interfaces\SegValidatorInterface;

abstract class AbstractSegment implements SegInterface
{
    protected SegmentData $elements;

    protected array $cache = [];

    protected SegValidatorInterface $validator;

    protected function __construct(SegmentData $elements)
    {
        $this->elements = $elements;
        $this->validator = new SegmentValidator;
    }

    abstract public static function blueprint(): DataGroups;

    /**
     * @psalm-suppress UnsafeInstantiation
     */
    public static function fromSegLine(Delimiter $delimiter, string $segLine): SegInterface
    {
        return new static(static::mapToBlueprint($delimiter, $segLine));
    }

    /**
     * @param int $dataGroup
     * @param int $element
     *
     * @return string|null
     */
    public function getValue($dataGroup, $element)
    {
        return $this->elements->getValueFromPosition($dataGroup, $element);
    }

    /**
     * @return SegValidatorInterface
     */
    public function getValidator()
    {
        return $this->validator;
    }

    public function getDelimiter(): Delimiter
    {
        return $this->elements->getDelimiter();
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

    public function toArray(): array
    {
        $result = [];
        foreach ($this->getGetterMethods() as $method) {
            if (null !== $value = $this->{$method}()) {
                $result[$method] = $value;
            }
        }
        return $result;
    }

    /**
     * @deprecated
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return $this->elements->toString();
    }

    /**
     * @return list<string>
     */
    protected function getGetterMethods(): array
    {
        if (isset($this->cache['getterMethods'])) {
            return $this->cache['getterMethods'];
        }

        $this->cache['getterMethods'] = [];
        foreach ((new \ReflectionClass(static::class))->getMethods() as $method) {
            if ($method->class === static::class && !$method->isStatic() && $method->isPublic()) {
                $this->cache['getterMethods'][] = $method->name;
            }
        }

        return $this->cache['getterMethods'];
    }

    protected static function mapToBlueprint(Delimiter $delimiter, string $segLine): SegmentData
    {
        $i = 0;
        $dataCollection = new DataGroups;
        $inputDataGroups = $delimiter->explodeSegments($segLine);
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

        return new SegmentData($dataCollection, $delimiter);
    }
}
