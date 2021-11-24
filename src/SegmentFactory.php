<?php

namespace Proengeno\Edifact;

use Proengeno\Edifact\Exceptions\SegValidationException;
use Proengeno\Edifact\Interfaces\DecimalConverter;
use Proengeno\Edifact\Interfaces\SegInterface;

final class SegmentFactory
{
    protected ?string $segmentNamespace;

    protected Delimiter $delimiter;

    /** @var class-string<SegInterface>|null */
    private ?string $fallback;

    /**
     * @param class-string<SegInterface>|null $fallback
     */
    public function __construct(string $segmentNamespace = null, Delimiter $delimiter = null, ?string $fallback = null)
    {
        $this->segmentNamespace = $segmentNamespace;
        $this->delimiter = $delimiter ?: new Delimiter;
        $this->fallback = $fallback;
    }

    /**
     * @param class-string<SegInterface> $fallback
     */
    public function addFallback(string $fallback): self
    {
        $this->fallback = $fallback;

        return $this;
    }

    public function fromSegline(string $segline): SegInterface
    {
        $segmentClass = $this->getSegmentClass($this->getSegname($segline));

        return $segmentClass::fromSegLine($this->delimiter, $segline);
    }

    public function fromAttributes(string $segmentName, array $attributes = [], string $method = 'fromAttributes'): SegInterface
    {
        $segmentClass = $this->getSegmentClass($segmentName);

        $segment = $segmentClass::$method(...$attributes);

        if ($segment instanceof DecimalConverter) {
            $segment->setDecimalSeparator($this->delimiter->getDecimal());
        }

        return $segment;
    }

    /**
     * @psalm-return class-string<SegInterface>
     */
    private function getSegmentClass(string $segmentName): string
    {
        $segmentClass = ucfirst(strtolower($segmentName));

        if ($this->segmentNamespace !== null) {
            $segmentClass = $this->segmentNamespace . '\\' . $segmentClass;
        }

        if (! is_subclass_of($segmentClass, SegInterface::class)) {
            if (null === $segmentClass = $this->fallback) {
                throw SegValidationException::unknown($this->getSegname($segmentName));
            }
        }

        return $segmentClass;
    }

    private function getSegname(string $segLine): string
    {
        return substr($segLine, 0, 3);
    }
}
