<?php 

namespace Proengeno\Edifact\Message;

class SegmentRegister
{
    private static $segmentNamespaces = [
        'Proengeno\\Edifact\\Message\\Segments\\'
    ];

    public static function getClassname($segment) {
        $segment = ucfirst(strtolower($segment));

        foreach (self::$segmentNamespaces as $segmentNamespace) {
            if (class_exists($segmentNamespace.$segment)) {
                return $segmentNamespace.$segment;
            }
        }
    }

    public static function addNamespace($namespace)
    {
        self::$segmentNamespaces[] = $namespace;
    }
}
