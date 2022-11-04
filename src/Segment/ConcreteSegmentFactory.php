<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Segment;

use Apfelfrisch\Edifact\Exceptions\InvalidEdifactContentException;
use Apfelfrisch\Edifact\Iterators\Stream\StreamMessageFactory;
use Apfelfrisch\Edifact\Message;

final class ConcreteSegmentFactory implements SegmentFactory
{
    /** @psalm-var array<string, class-string<Segment>> */
    private array $segmentClasses = [];

    /** @psalm-var class-string<Segment>|null */
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
     * @psalm-param $segmentClass class-string<Segment>
     */
    public function addSegment(string $name, string $segmentClass): self
    {
        if (is_subclass_of($segmentClass, Segment::class)) {
            $this->segmentClasses[substr(strtoupper($name), 0, 3)] = $segmentClass;
        }

        return $this;
    }

    /**
     * @psalm-param class-string<Segment> $fallback
     */
    public function addFallback(string $fallback): self
    {
        $this->fallback = $fallback;

        return $this;
    }

    public function markAsDefault(): void
    {
        Message::setFactory(new StreamMessageFactory($this));
    }

    public function build(string $segline): Segment
    {
        /** @psalm-var class-string<Segment> */
        $segmentClass = $this->getClassname(substr($segline, 0, 3));

        $segment = $segmentClass::fromSegLine($this->parser, $segline);

        return $segment;
    }

    /*
     * @psalm-return class-string<Segment>
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
