<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Segment;

use Apfelfrisch\Edifact\Exceptions\InvalidEdifactContentException;

final class ConcreteSegmentFactory implements SegmentFactory
{
    /** @var array<string, class-string<Segment>> */
    private array $segmentClasses = [];

    /** @var class-string<Segment>|null */
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
            $this->segmentClasses[strtoupper(substr($name, 0, 3))] = $segmentClass;
        }

        return $this;
    }

    /**
     * @param class-string<Segment> $fallback
     */
    public function addFallback(string $fallback): self
    {
        $this->fallback = $fallback;

        return $this;
    }

    public function build(string $segline): Segment
    {
        $segmentClass = $this->getClassname(substr($segline, 0, 3));

        // @mago-expect analysis:possibly-static-access-on-interface — the Segment interface declares fromSegLine()
        return $segmentClass::fromSegLine($this->parser, $segline);
    }

    /**
     * @return class-string<Segment>
     */
    public function getClassname(string $segmentName): string
    {
        $segmentName = strtoupper($segmentName);

        return (
            $this->segmentClasses[$segmentName] ?? $this->fallback ?? throw InvalidEdifactContentException::segmentUnknown(
                $segmentName,
            )
        );
    }
}
