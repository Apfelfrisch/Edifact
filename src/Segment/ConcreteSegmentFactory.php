<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Segment;

use Apfelfrisch\Edifact\Exceptions\InvalidEdifactContentException;

final class ConcreteSegmentFactory implements SegmentFactory
{
    /** @var array<string, class-string<Segment>> */
    private array $segmentClasses = [];

    /**
     * Resolved classnames per raw (case-preserving) segline prefix.
     *
     * @var array<string, class-string<Segment>>
     */
    private array $resolvedClasses = [];

    /** @var class-string<Segment>|null */
    private null|string $fallback = null;

    private UnaSegment $unaSegment;

    private SeglineParser $parser;

    public function __construct(null|UnaSegment $unaSegment = null)
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
     * Returns a factory using the given UNA segment, leaving this instance
     * untouched. The same instance is returned when the UNA characters are
     * unchanged, so the parser's blueprint cache is retained.
     */
    public function withUnaSegment(UnaSegment $unaSegment): self
    {
        if ($unaSegment->toString() === $this->unaSegment->toString()) {
            return $this;
        }

        $factory = clone $this;
        $factory->setUnaSegment($unaSegment);

        return $factory;
    }

    /**
     * @param class-string<Segment> $segmentClass
     */
    public function addSegment(string $name, string $segmentClass): self
    {
        if (is_subclass_of($segmentClass, Segment::class)) {
            $this->segmentClasses[strtoupper(substr($name, 0, 3))] = $segmentClass;
            $this->resolvedClasses = [];
        }

        return $this;
    }

    /**
     * @param class-string<Segment> $fallback
     */
    public function addFallback(string $fallback): self
    {
        $this->fallback = $fallback;
        $this->resolvedClasses = [];

        return $this;
    }

    public function build(string $segline): Segment
    {
        $prefix = substr($segline, 0, 3);

        $segmentClass = $this->resolvedClasses[$prefix] ?? null;

        if ($segmentClass === null) {
            $segmentClass = $this->getClassname($prefix);
            $this->resolvedClasses[$prefix] = $segmentClass;
        }

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
