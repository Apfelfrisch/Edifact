<?php 

namespace Proengeno\Edifact;

use Proengeno\Edifact\Exceptions\EdifactException;

class EdifactFactory
{
    public static function fromString($edifactString)
    {
        $className = self::getClassname($edifactString);

        return call_user_func_array("$className::fromString", [$edifactString]);
    }

    private static function getClassname($edifactString) 
    {
        $classname = self::getMessageType($edifactString);
        if ($typeReferenz = self::getTypeReferenz($edifactString) ) {
            $classname .= '_' . $typeReferenz;
        }
        return EdifactRegistrar::getMessage($classname);
    }

    private static function getMessageType($edifactString)
    {
        if (!preg_match('/UNH\+(.*?)\+(.*?)\:/', $edifactString, $matches) || empty($matches[2])) {
            throw EdifactException::massageTypeNotFound();
        }

        return $matches[2];
    }

    private static function getTypeReferenz($edifactString)
    {
        if (!preg_match('/RFF\+Z13\:(.*?)\'/', $edifactString, $matches) || empty($matches[1])) {
            throw EdifactException::referenceNotFound();
        }

        return $matches[1];
    }
}
