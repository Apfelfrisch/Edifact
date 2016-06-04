<?php

namespace Proengeno\Edifact\Exceptions;

use RuntimeException;

class EdifactException extends RuntimeException
{
    public static function segmentUnknown($segment)
    {
        return new static("Segment not registered: '$segment'");
    }

    public static function messageUnknown($message)
    {
        return new static("Message not registered: '$message'");
    }
}
