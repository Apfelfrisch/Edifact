<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact;

use Apfelfrisch\Edifact\Exceptions\EdifactException;
use Apfelfrisch\Edifact\Interfaces\SegInterface;
use Apfelfrisch\Edifact\GenericSegment;

final class SegmentFactory
{
    /** @psalm-var array<string, class-string<SegInterface>> */
    private array $segmentClasses = [];

    /** @psalm-var class-string<SegInterface>|null */
    private ?string $fallback = null;

    private static ?self $defaultFactory = null;

    private UnaSegment $unaSegment;

    private SeglineParser $parser;

    public function __construct(?UnaSegment $unaSegment = null)
    {
        $this->unaSegment = $unaSegment ?? UnaSegment::getDefault();
        $this->parser = new SeglineParser($this->unaSegment);
    }

    public static function setDefault(self $defaultFactory): void
    {
        self::$defaultFactory = $defaultFactory;
    }

    public static function fromDefault(bool $withFallback = true): self
    {
        if (self::$defaultFactory === null) {
            self::$defaultFactory = new self;
        }

        $instance = clone(self::$defaultFactory);

        if ($withFallback) {
            $instance->addFallback(GenericSegment::class);
        }

        return $instance;
    }

    public function setUnaSegment(UnaSegment $unaSegment): void
    {
        $this->unaSegment = $unaSegment;
        $this->parser = new SeglineParser($this->unaSegment);
    }

    /**
     * @psalm-param $segmentClass class-string<SegInterface>
     */
    public function addSegment(string $name, string $segmentClass): self
    {
        if (is_subclass_of($segmentClass, SegInterface::class)) {
            $this->segmentClasses[substr(strtoupper($name), 0, 3)] = $segmentClass;
        }
        return $this;
    }

    /**
     * @psalm-param class-string<SegInterface> $fallback
     */
    public function addFallback(string $fallback): self
    {
        $this->fallback = $fallback;

        return $this;
    }

    public function markAsDefault(): void
    {
        self::setDefault($this);
    }

    public function build(string $segline): SegInterface
    {
        /** @psalm-var class-string<SegInterface> */
        $segmentClass = $this->getClassname(substr($segline, 0, 3));

        $segment = $segmentClass::fromSegLine($this->parser, $segline);

        return $segment;
    }

    /*
     * @psalm-return class-string<SegInterface>
     */
    public function getClassname(string $segmentName): string
    {
        $segmentName = strtoupper($segmentName);

        if (null === $segmentClass = $this->segmentClasses[$segmentName] ?? null) {
            if (null === $segmentClass = $this->fallback) {
                throw EdifactException::segmentUnknown($segmentName);
            }
        }

        return $segmentClass;
    }
}
