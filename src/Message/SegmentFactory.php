<?php

namespace Proengeno\Edifact\Message;

use Proengeno\Edifact\Exceptions\ValidationException;
use Proengeno\Edifact\Interfaces\SegInterface;

final class SegmentFactory
{
    protected ?string $segmentNamespace;

    protected Delimiter $delimiter;

    /** @var class-string<SegInterface>|null */
    private ?string $genericSegment;

    /**
     * @param class-string<SegInterface>|null $genericSegment
     */
    public function __construct(string $segmentNamespace = null, Delimiter $delimiter = null, ?string $genericSegment = null)
    {
        $this->segmentNamespace = $segmentNamespace;
        $this->delimiter = $delimiter ?: new Delimiter;
        $this->genericSegment = $genericSegment;
    }

    public function fromSegline(string $segline): SegInterface
    {
        $segment = $this->getSegmentClass($this->getSegname($segline));

        return $segment::fromSegLine($this->delimiter, $segline);
    }

    public function fromAttributes(string $segmentName, array $attributes = [], string $method = 'fromAttributes'): SegInterface
    {
        $segment = $this->getSegmentClass($segmentName);

        return $segment::$method($this->delimiter, ...$attributes);
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
            if (null === $segmentClass = $this->genericSegment) {
                throw new ValidationException("Unknown Segment '" . $this->getSegname($segmentName) . "'");
            }
        }

        return $segmentClass;
    }

    private function getSegname(string $segLine): string
    {
        return substr($segLine, 0, 3);
    }
}
