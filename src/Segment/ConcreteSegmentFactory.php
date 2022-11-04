<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Segment;

use Apfelfrisch\Edifact\Exceptions\InvalidEdifactContentException;

final class ConcreteSegmentFactory implements SegmentFactory
{
    /** @phpstan-var array<string, class-string<Segment>> */
    private array $segmentClasses = [];

    /** @phpstan-var class-string<Segment>|null */
    private ?string $fallback = null;

    private UnaSegment $unaSegment;

    private SeglineParser $parser;

    public function __construct(?UnaSegment $unaSegment = null)
    {
        $this->unaSegment = $unaSegment ?? UnaSegment::getDefault();
        $this->parser = new SeglineParser($this->unaSegment);
    }

    public function setUnaSegment(UnaSegment $unaSegment): void
    {
        $this->unaSegment = $unaSegment;
        $this->parser = new SeglineParser($this->unaSegment);
    }

    /**
     * @param class-string<Segment> $segmentClass
     */
    public function addSegment(string $name, string $segmentClass): self
    {
        if (is_subclass_of($segmentClass, Segment::class)) {
            $this->segmentClasses[substr(strtoupper($name), 0, 3)] = $segmentClass;
        }

        return $this;
    }

    /**
     * @phpstan-param class-string<Segment> $fallback
     */
    public function addFallback(string $fallback): self
    {
        $this->fallback = $fallback;

        return $this;
    }

    public function build(string $segline): Segment
    {
        /** @var class-string<Segment> */
        $segmentClass = $this->getClassname(substr($segline, 0, 3));

        $segment = $segmentClass::fromSegLine($this->parser, $segline);

        return $segment;
    }

    /*
     * @phpstan-return class-string<Segment>
     */
    public function getClassname(string $segmentName): string
    {
        $segmentName = strtoupper($segmentName);

        if (null === $segmentClass = $this->segmentClasses[$segmentName] ?? null) {
            if (null === $segmentClass = $this->fallback) {
                throw InvalidEdifactContentException::segmentUnknown($segmentName);
            }
        }

        return $segmentClass;
    }
}
