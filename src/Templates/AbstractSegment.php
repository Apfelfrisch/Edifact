<?php

namespace Proengeno\Edifact\Templates;

use Proengeno\Edifact\Exceptions\SegValidationException;
use Proengeno\Edifact\Message\DataGroupCollection;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Interfaces\SegInterface;
use Proengeno\Edifact\Validation\SegmentValidator;
use Proengeno\Edifact\Interfaces\SegValidatorInterface;

abstract class AbstractSegment implements SegInterface
{
    /** @var array<string, array<string, string>> */
    protected static $validationBlueprint = [];

    protected static SegValidatorInterface|null $buildValidator = null;

    protected static Delimiter|null $buildDelimiter = null;

    protected DataGroupCollection $elements;

    protected array $cache = [];

    protected Delimiter $delimiter;

    protected SegValidatorInterface $validator;

    protected function __construct(DataGroupCollection $elements)
    {
        $this->elements = $elements;
        $this->delimiter = static::getBuildDelimiter();
        $this->validator = static::$buildValidator ?? new SegmentValidator;
    }

    /**
     * @param string $segLine
     *
     * @return static
     *
     * @psalm-suppress UnsafeInstantiation
     */
    public static function fromSegLine($segLine)
    {
        return new static(static::mapToBlueprint($segLine));
    }

    /**
     * @return void
     */
    public static function setBuildDelimiter(?Delimiter $delimiter)
    {
        self::$buildDelimiter = $delimiter;
    }

    /**
     * @return Delimiter
     */
    public static function getBuildDelimiter()
    {
        return self::$buildDelimiter ?? new Delimiter;
    }

    /**
     * @return void
     */
    public static function setBuildValidator(SegValidatorInterface $buildValidator = null)
    {
        self::$buildValidator = $buildValidator;
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
        return $this->delimiter;
    }

    public function name(): string
    {
        if (null === $name = $this->getValue(0,0)) {
            throw SegValidationException::forKey('name', "Segment is empty");
        }

        return $name;
    }

    public function validate(): self
    {
        $this->validator->validate(static::$validationBlueprint, $this->elements);

        return $this;
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
     * @param string $attribute
     *
     * @return string|null
     */
    public function __get($attribute)
    {
        try {
            if (in_array($attribute, $this->getGetterMethods())) {
                return $this->$attribute();
            }
        } catch (\Throwable) { }

        return null;
    }

    /**
     * @return list<string>
     */
    protected function getGetterMethods()
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

    protected static function mapToBlueprint(string $segLine): DataGroupCollection
    {
        $i = 0;
        $dataCollection = new DataGroupCollection(static::getBuildDelimiter());
        $inputDataGroups = static::getBuildDelimiter()->explodeSegments($segLine);
        foreach (static::$validationBlueprint as $BpDataKey => $BPdataGroups) {
            $inputElement = [];
            if (isset($inputDataGroups[$i])) {
                $inputElement = static::getBuildDelimiter()->explodeElements($inputDataGroups[$i]);
            }

            $j = 0;
            foreach (array_keys($BPdataGroups) as $key) {
                $dataCollection->addValue($BpDataKey, $key, isset($inputElement[$j]) ? $inputElement[$j] : null);
                $j++;
            }
            $i++;
        }

        return $dataCollection;
    }
}
