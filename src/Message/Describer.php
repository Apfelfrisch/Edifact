<?php

namespace Proengeno\Edifact\Message;

use Proengeno\Edifact\Exceptions\ValidationException;

class Describer
{
    private static $distincInstances = [];
    private $description = [];

    private function __construct($file = null)
    {
        if ($file !== null) {
            if (!is_file($file)) {
                throw new \InvalidArgumentException("$file not found.");
            }
            $this->description = include($file);
        }
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

        if ($this->description === null) {
            throw new ValidationException('No Description set.');
        }

        throw new ValidationException("Description '$key' not found.");
    }
}
