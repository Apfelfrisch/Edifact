<?php

namespace Apfelfrisch\Edifact;

use Apfelfrisch\Edifact\Exceptions\SegValidationException;
use Apfelfrisch\Edifact\Interfaces\SegInterface;
use Apfelfrisch\Edifact\Segments\Fallback;

final class SegmentFactory
{
    /** @var array<string, class-string<SegInterface>> */
    private array $segmentClasses = [];

    /** @var class-string<SegInterface>|null */
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
            $instance->addFallback(Fallback::class);
        }

        return $instance;
    }

    /**
     * @param $segmentClass class-string<SegInterface>
     */
    public function addSegment(string $name, string $segmentClass): self
    {
        if (is_subclass_of($segmentClass, SegInterface::class)) {
            $this->segmentClasses[strtoupper($name)] = $segmentClass;
        }
        return $this;
    }

    /**
     * @param class-string<SegInterface> $fallback
     */
    public function addFallback(string $fallback): self
    {
        $this->fallback = $fallback;

        return $this;
    }

    public function build(string $segline, Delimiter $delimiter): SegInterface
    {
        $segmentName = strtoupper(substr($segline, 0, 3));

        if (null === $segmentClass = $this->segmentClasses[$segmentName] ?? null) {
            if (null === $segmentClass = $this->fallback) {
                throw SegValidationException::unknown($segmentName);
            }
        }

        return $segmentClass::fromSegLine($delimiter, $segline);
    }
}
