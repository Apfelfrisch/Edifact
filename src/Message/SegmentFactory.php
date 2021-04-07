<?php

namespace Proengeno\Edifact\Message;

use Proengeno\Edifact\Exceptions\ValidationException;
use Proengeno\Edifact\Interfaces\SegInterface;

class SegmentFactory
{
    /** @var string|null */
    protected $segmentNamespace;

    /** @var Delimiter */
    protected $delimiter;

    /** @var class-string<SegInterface>|null */
    private $genericSegment;

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
        return $this->instanciateSegment($this->getSegname($segline), 'fromSegLine', $segline);
    }

    public function fromAttributes(string $segmentName, array $attributes = [], string $method = 'fromAttributes'): SegInterface
    {
        return $this->instanciateSegment($segmentName, $method, $attributes);
    }

    private function instanciateSegment(string $segmentName, string $method, string|array $attributes): SegInterface
    {
        if (!is_array($attributes)) {
            $attributes = [$attributes];
        }

        $segment = $this->getSegmentClass($segmentName);

        if ($segment !== null && is_callable([$segment, 'setBuildDelimiter'])) {
            call_user_func_array([$segment, 'setBuildDelimiter'], [$this->delimiter]);

            return call_user_func_array([$segment, $method], $attributes);
        }

        if ($this->genericSegment !== null) {
            call_user_func_array([$this->genericSegment, 'setBuildDelimiter'], [$this->delimiter]);

            return call_user_func_array([$this->genericSegment, $method], $attributes);
        }

        throw new ValidationException("Unknown Segment '" . $segmentName . "'");
    }

    private function getSegmentClass(string $segmentName): ?string
    {
        if ($this->segmentNamespace === null) {
            return null;
        }
        return $this->segmentNamespace . '\\' . ucfirst(strtolower($segmentName));
    }

    private function getSegname(string $segLine): string
    {
        return substr($segLine, 0, 3);
    }
}
