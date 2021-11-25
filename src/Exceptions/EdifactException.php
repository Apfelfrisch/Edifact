<?php

namespace Apfelfrisch\Edifact\Exceptions;

use RuntimeException;

class EdifactException extends RuntimeException
{
    /**
     * @param string $segment
     *
     * @return static
     *
     * @psalm-suppress UnsafeInstantiation
     */
    public static function segmentUnknown($segment)
    {
        return new static("Segment not registered: '$segment'");
    }

    /**
     * @param string $message
     *
     * @return static
     *
     * @psalm-suppress UnsafeInstantiation
     */
    public static function messageUnknown($message)
    {
        return new static("Message not registered: '$message'");
    }
}
