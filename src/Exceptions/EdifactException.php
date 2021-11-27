<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Exceptions;

use RuntimeException;

class EdifactException extends RuntimeException
{
    public static function segmentUnknown(string $segment): self
    {
        return new self("Segment not registered: ['$segment']");
    }
}
