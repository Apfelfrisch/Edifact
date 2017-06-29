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
                throw new \InvalidArgumentException("Description-File '$file' not found.");
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
        if (is_null($key)) {
            return false;
        }

        if (array_key_exists($key, $this->description)) {
            return true;
        }

        $matchedDescription = $this->description;
        foreach (explode('.', $key) as $segment) {
            if (! is_array($matchedDescription) || ! array_key_exists($segment, $matchedDescription)) {
                return false;
            }

            $matchedDescription = $matchedDescription[$segment];
        }

        return true;
    }

    public function get($key)
    {
        if ($this->description === null) {
            throw new ValidationException('No Description set.');
        }

        if (is_null($key)) {
            return null;
        }

        if (isset($this->description[$key])) {
            return $this->description[$key];
        }

        $matchedDescription = $this->description;
        foreach (explode('.', $key) as $segment) {
            if (! is_array($matchedDescription) || ! array_key_exists($segment, $matchedDescription)) {
                throw new ValidationException("Description '$key' not found.");
            }

            $matchedDescription = $matchedDescription[$segment];
        }

        return $matchedDescription;
    }
}
