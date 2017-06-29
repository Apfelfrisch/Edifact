<?php

namespace Proengeno\Edifact\Message;

use Proengeno\Edifact\Exceptions\ValidationException;

class Describer
{
    private static $distincInstances = [];
    private $description = [];
    private $throwException;
    private $defaultDescription;

    private function __construct($file = null, $throwException = true, $defaultDescription = null)
    {
        if ($file !== null) {
            if (!is_file($file)) {
                throw new \InvalidArgumentException("Description-File '$file' not found.");
            }
            $this->description = include($file);
        }
        $this->throwException = $throwException;
        $this->defaultDescription = $defaultDescription;
    }

    public static function build($file)
    {
        if (!isset(self::$distincInstances[md5($file)])) {
            self::$distincInstances[md5($file)] = new self($file);
        }

        return self::$distincInstances[md5($file)];
    }

    public static function buildWithDefaultDescription($file, $description = null)
    {
        if (!isset(self::$distincInstances[md5($file)])) {
            self::$distincInstances[md5($file)] = new self($file, false, $description);
        }

        return self::$distincInstances[md5($file)];
    }

    public static function clean()
    {
        self::$distincInstances = [];
    }

    public function has($key)
    {
        if (is_null($key)) {
            return false;
        }

        if (array_key_exists($key, $this->description)) {
            return true;
        }

        return !! $this->findDescription($key);
    }

    public function get($key, $default = null)
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

        if (null === $description = $this->findDescription($key)) {
            if ($default !== null) {
                return $default;
            }
            if ($this->throwException == true) {
                throw new ValidationException("Description '$key' not found.");
            }
            return $this->defaultDescription;
        }

        return $description;
    }

    private function findDescription($key)
    {
        $matchedDescription = $this->description;
        foreach (explode('.', $key) as $segment) {
            if (! is_array($matchedDescription) || ! array_key_exists($segment, $matchedDescription)) {
                return null;
            }

            $matchedDescription = $matchedDescription[$segment];
        }

        return $matchedDescription;
    }
}
