<?php

namespace Proengeno\Edifact;

use Proengeno\Edifact\Exceptions\EdifactException;

class EdifactRegistrar
{
    private static $segments = [
        'AGR' => Message\Segments\Agr::class,
        'BGM' => Message\Segments\Bgm::class,
        'CAV' => Message\Segments\Cav::class,
        'CCI' => Message\Segments\Cci::class,
        'DTM' => Message\Segments\Dtm::class,
        'IDE' => Message\Segments\Ide::class,
        'IMD' => Message\Segments\Imd::class,
        'LIN' => Message\Segments\Lin::class,
        'LOC' => Message\Segments\Loc::class,
        'NAD' => Message\Segments\Nad::class,
        'QTY' => Message\Segments\Qty::class,
        'RFF' => Message\Segments\Rff::class,
        'SEQ' => Message\Segments\Seq::class,
        'UNA' => Message\Segments\Una::class,
        'UNB' => Message\Segments\Unb::class,
        'UNH' => Message\Segments\Unh::class,
        'UNS' => Message\Segments\Uns::class,
        'UNT' => Message\Segments\Unt::class,
        'UNZ' => Message\Segments\Unz::class,
    ];
    private static $messages = [
        'ORDERS_17201' => Message\Messages\Orders_17201::class,
        'ORDERS_17103' => Message\Messages\Orders_17103::class,
        'UTILMD_11019' => Message\Messages\Utilmd_11019::class,
    ];

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
        throw EdifactException::segmentUnknown($key);
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
        throw EdifactException::messageUnknown($key);
    }
}
