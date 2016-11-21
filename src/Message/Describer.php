<?php

namespace Proengeno\Edifact\Message;

class Describer
{
    private static $distincInstances = [];
    private $description;

    private function __construct($file)
    {
        if (!is_file($file)) {
            throw new \InvalidArgumentException("$file not found.");
        }
        $this->description = include($file);
    }

    public static function build($file)
    {
        if (!isset(self::$distincInstances[md5($file)])) {
            self::$distincInstances[md5($file)] = new self($file);
        }

        return self::$distincInstances[md5($file)];
    }

    public function has($key)
    {
        return isset($this->description[$key]);
    }

    public function get($key)
    {
        if ($this->has($key)) {
            return $this->description[$key];
        }

        return null;
    }
}
