<?php 

namespace Proengeno\Edifact\Exceptions;

use Exception;

class EdifactParseException extends Exception 
{
    public static function messageNotFound($messageType, $typeReferenz)
    {
        return new static('EdifactMessage ' . $messageType . '_' . $typeReferenz . ' not found');
    }

    public static function couldNotFindMassageType()
    {
        return new static('Konnte Narichtentyp nicht aus Naricht extrahieren.');
    }

    public static function couldNotFindMassageReference()
    {
        return new static('Konnte Narichtentyp-Referenz nicht aus Edifact extrahieren.');
    }
}

