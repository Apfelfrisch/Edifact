<?php

namespace Proengeno\Edifact\Message;

use Proengeno\Edifact\Exceptions\ValidationException;
use Proengeno\Edifact\Interfaces\SegInterface;

final class SegmentFactory
{
    private ?string $segmentNamespace;

    /** @var class-string<SegInterface>|null */
    private ?string $genericSegment;

    /**
     * @param class-string<SegInterface>|null $genericSegment
     */
    public function __construct(string $segmentNamespace = null, ?string $genericSegment = null)
    {
        $this->segmentNamespace = $segmentNamespace;
        $this->genericSegment = $genericSegment;
    }

    public function fromSegline(string $segline, Delimiter $delimiter = null): SegInterface
    {
        $delimiter ??= new Delimiter;
        $segmentName = $this->getSegname($segline);
        $segmentClass = $this->getSegmentClass($segmentName);

        if ($segmentClass !== null) {
            return $segmentClass::fromSegline($segline, $delimiter);
        }

        if ($this->genericSegment !== null) {
            return $this->genericSegment::fromSegline($segline, $delimiter);
        }

        throw new ValidationException("Unknown Segment '" . $segmentName . "'");
    }

    public function fromAttributes(string $segmentName, array $attributes = [], string $method = 'fromAttributes'): SegInterface
    {
        $segmentClass = $this->getSegmentClass($segmentName);

        if ($segmentClass !== null) {
            return call_user_func_array([$segmentClass, $method], $attributes);
        }

        if ($this->genericSegment !== null) {
            return call_user_func_array([$this->genericSegment, $method], $attributes);
        }

        throw new ValidationException("Unknown Segment '" . $segmentName . "'");
    }

    /**
     * @return class-string<SegInterface>|null
     */
    private function getSegmentClass(string $segmentName): ?string
    {
        if ($this->segmentNamespace === null) {
            return null;
        }

        $fullClass = $this->segmentNamespace . '\\' . ucfirst(strtolower($segmentName));

        if (! is_subclass_of($fullClass, SegInterface::class)) {
            return null;
        }

        return $fullClass;
    }

    private function getSegname(string $segLine): string
    {
        return substr($segLine, 0, 3);
    }
}
