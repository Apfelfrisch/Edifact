<?php 

namespace Proengeno\Edifact;

use Proengeno\Edifact\Exceptions\ValidationException;

class EdifactFactory
{
    public static function fromStream(EdifactFile $stream)
    {
        $className = self::getClassname($stream);

        return new $className($stream);
    }

    private static function getClassname($edifactStream) 
    {
        $classname = self::getMessageType($edifactStream);
        if ($typeReferenz = self::getTypeReferenz($edifactStream) ) {
            $classname .= '_' . $typeReferenz;
        }
        return EdifactRegistrar::getMessage($classname);
    }

    private static function getMessageType($edifactStream)
    {
        while ($segment = $edifactStream->getSegment()) {
            if (preg_match('/UNH\+(.*?)\+(.*?)\:/', $segment, $matches) || empty($matches[2])) {
                return $matches[2];
            }
        }

        throw ValidationException::massageTypeNotFound();
    }

    private static function getTypeReferenz($edifactStream)
    {
        while ($segment = $edifactStream->getSegment()) {
            if (preg_match('/RFF\+Z13\:(.*?)\'/', $edifactStream, $matches) || empty($matches[1])) {
                return $matches[1];
            }
        }
        throw ValidationException::referenceNotFound();
    }
}
