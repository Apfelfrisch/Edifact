<?php

namespace Proengeno\Edifact\Templates;

use Proengeno\Edifact\Exceptions\SegValidationException;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Interfaces\SegInterface;
use Proengeno\Edifact\Validation\SegmentValidator;
use Proengeno\Edifact\Interfaces\SegValidatorInterface;

abstract class AbstractSegment implements SegInterface
{
    /** @var array<string, array<string, string>> */
    protected static $validationBlueprint = [];

    /** @var array<string, array<string, null|string>> */
    protected array $elements = [];

    protected array $cache = [];

    /** @param array<string, array<string, null|string>> $elements */
    protected function __construct(array $elements)
    {
        $this->elements = $elements;
    }

    public static function fromSegline(string $segLine, ?Delimiter $delimiter = null): SegInterface
    {
        $delimiter ??= new Delimiter;

        $elementKeys = array_keys(static::$validationBlueprint);
        $segmentKeys = array_map(function($blueprintRow) {
            return array_keys($blueprintRow);
        }, array_values(static::$validationBlueprint));

        $i = 0;
        $segements = [];
        foreach ($delimiter->explodeSegments($segLine) as $dataGroup) {
            $elements = $delimiter->explodeElements($dataGroup);

            $j = 0;
            foreach ($elements as $element) {
                if (! isset($elementKeys[$i])) {
                    continue;
                }
                if (! isset($segmentKeys[$i][$j])) {
                    continue;
                }
                $segements[$elementKeys[$i]][$segmentKeys[$i][$j]] = $element;
                $j++;
            }

            $i++;
        }

        /** @psalm-suppress UnsafeInstantiation */
        return new static($segements);
    }

    public function getValue(string $dataGroupKey, string $dataKey): string|null
    {
        return $this->elements[$dataGroupKey][$dataKey] ?? null;
    }

    public function name(): string
    {
        if (null === $name = array_values(array_values($this->elements)[0])[0] ?? null) {
            throw SegValidationException::forKey('name', "Segment is empty");
        }

        return $name;
    }

    public function validate(?SegValidatorInterface $validator = null): self
    {
        $validator ??= new SegmentValidator;

        $validator->validate(static::$validationBlueprint, $this->elements);

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

    public function toString(?Delimiter $delimiter = null): string
    {
        $delimiter ??= new Delimiter;

        $trimDataGroups = static function (array $dataGroups): array {
            $reversed = array_reverse($dataGroups);

            foreach ($reversed as $key => $value) {
                if (!empty($value)) {
                    break;
                }
                unset($reversed[$key]);
            }

            return array_reverse($reversed);
        };

        $implodeElements = static fn(array $dataGroups): string
            => implode($delimiter->getData(),
                array_map(fn(?string $value): string
                    => $delimiter->terminate((string)$value), $trimDataGroups($dataGroups)
                )
            );

        return implode($delimiter->getDataGroup(), array_map($implodeElements, $this->elements)) . $delimiter->getSegment();
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
     * @param string $name
     * @param array $arguments
     *
     * @return string|null
     */
    public function __call($name, $arguments)
    {
        if (strpos($name, 'get') === false) {
            throw new \BadMethodCallException;
        }

        $pattern = substr($name, 3);

        foreach ($this->elements as $element) {
            if (array_key_exists($pattern, $element)) {
                return $element[$pattern];
            }
        }

        throw new \BadMethodCallException;
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
}
