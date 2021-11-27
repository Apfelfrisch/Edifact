<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact;

use Apfelfrisch\Edifact\Exceptions\EdifactException;
use Apfelfrisch\Edifact\Interfaces\SegInterface;
use Apfelfrisch\Edifact\Segments\Generic;

final class SegmentFactory
{
    /** @psalm-var array<string, class-string<SegInterface>> */
    private array $segmentClasses = [];

    /** @psalm-var class-string<SegInterface>|null */
    private ?string $fallback = null;

    private static ?self $defaultFactory = null;

    public static function withDefaultDegments(bool $withFallback = true): self
    {
        if (self::$defaultFactory === null) {
            $defaultPath = __DIR__ . '/Segments/';

            self::$defaultFactory = new self;

            foreach (glob($defaultPath."???.php") as $segmentClassFile) {
                $classBasename = basename($segmentClassFile, '.php');
                self::$defaultFactory->addSegment($classBasename, '\\Apfelfrisch\\Edifact\\Segments\\' . $classBasename);
            }
        }

        $instance = clone(self::$defaultFactory);

        if ($withFallback) {
            $instance->addFallback(Generic::class);
        }

        return $instance;
    }

    /**
     * @psalm-param $segmentClass class-string<SegInterface>
     */
    public function addSegment(string $name, string $segmentClass): self
    {
        if (is_subclass_of($segmentClass, SegInterface::class)) {
            $this->segmentClasses[strtoupper($name)] = $segmentClass;
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

    public function build(string $segline, Delimiter $delimiter): SegInterface
    {
        /** @psalm-var class-string<SegInterface> */
        $segmentClass = $this->getClassname(substr($segline, 0, 3));

        return $segmentClass::fromSegLine($delimiter, $segline);
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
