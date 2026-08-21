<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Exceptions;

final class InvalidEdifactContentException extends EdifactException
{
    public static function segmentUnknown(string $segment): self
    {
        return new self("Segment not registered: ['{$segment}']");
    }

    public static function messageUnknown(string $key): self
    {
        return new self("Unknown message key [{$key}]");
    }

    public static function invalidUnaSegment(string $segmentString): self
    {
        return new self("Invalid Una Segment: ['{$segmentString}']");
    }

    public static function noSegmentAvailable(): self
    {
        return new self('No Segment available.');
    }
}
