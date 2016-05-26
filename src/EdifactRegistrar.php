<?php

namespace Proengeno\Edifact;

use Proengeno\Edifact\Exceptions\ValidationException;

class EdifactRegistrar
{
    private static $segments = [];
    private static $messages = [];

    public static function addSegement($key, $path)
    {
        static::$segments[strtoupper($key)] = $path;
    }

    public static function getSegment($key)
    {
        $key = strtoupper($key);
        if (isset(static::$segments[$key])) {
            return static::$segments[$key];
        }
        throw ValidationException::segmentUnknown($key);
    }

    public static function addMessage($key, $path)
    {
        static::$messages[strtoupper($key)] = $path;
    }

    public static function getMessage($key)
    {
        $key = strtoupper($key);
        if (isset(static::$messages[$key])) {
            return static::$messages[$key];
        }
        throw ValidationException::messageUnknown($key);
    }
}
