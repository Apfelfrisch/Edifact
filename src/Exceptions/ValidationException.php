<?php

namespace Proengeno\Edifact\Exceptions;

use RuntimeException;

class ValidationException extends RuntimeException
{
    public static function segmentUnknown($segment)
    {
        return new static("Edifact-Segment nicht registriert: '$segment'");
    }

    public static function messageUnknown($message)
    {
        return new static("Edifact-Naricht nicht registriert: '$message'");
    }

    public static function massageTypeNotFound()
    {
        return new static('Konnte Narichtentyp nicht aus Naricht extrahieren.');
    }

    public static function referenceNotFound()
    {
        return new static('Konnte Narichtentyp-Referenz nicht aus Edifact extrahieren.');
    }
}
