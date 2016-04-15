<?php 

namespace Proengeno\Edifact;

use Proengeno\Edifact\Exceptions\EdifactParseException;

class EdifactFactory
{
    public static $mappings = [
        'orders_17201' => Message\Messages\Orders_17201::class,
        'orders_17103' => Message\Messages\Orders_17103::class,
        'utilmd_11019' => Message\Messages\Utilmd_11019::class,
    ];

    public static function fromString($edifactString)
    {
        $className = self::getClassname($edifactString);

        return call_user_func_array("$className::fromString", [$edifactString]);
    }

    private static function getClassname($edifactString) 
    {
        $messageType = self::getMessageType($edifactString);
        $typeReferenz = self::getTypeReferenz($edifactString);

        $mapKey = strtolower($messageType . '_' . $typeReferenz);

        if (isset(self::$mappings[$mapKey])) {
            return self::$mappings[$mapKey];
        }

        throw EdifactParseException::messageNotFound($messageType, $typeReferenz);
    }

    private static function getMessageType($edifactString)
    {
        if (!preg_match('/UNH\+(.*?)\+(.*?)\:/', $edifactString, $matches) || empty($matches[2])) {
            throw EdifactParseException::couldNotFindMassageType();
        }

        return $matches[2];
    }

    private static function getTypeReferenz($edifactString)
    {
        if (!preg_match('/RFF\+Z13\:(.*?)\'/', $edifactString, $matches) || empty($matches[1])) {
            throw EdifactParseException::couldNotFindMassageReference();
        }

        return $matches[1];
    }
}
