<?php

namespace Proengeno\Edifact\Message;

use Proengeno\Edifact\Exceptions\ValidationException;

class Describer
{
    private static array $distincInstances = [];
    private array $description = [];
    private bool $throwException;
    private ?string $defaultDescription;

    /**
     * @param string $file
     * @param bool $throwException
     * @param string|null $defaultDescription
     */
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

    /**
     * @param string $file
     *
     * @return self
     */
    public static function build($file)
    {
        if (!isset(self::$distincInstances[md5($file)])) {
            self::$distincInstances[md5($file)] = new self($file);
        }

        return self::$distincInstances[md5($file)];
    }

    /**
     * @param string $file
     * @param ?string $description
     *
     * @return self
     */
    public static function buildWithDefaultDescription($file, $description = null)
    {
        if (!isset(self::$distincInstances[md5($file)])) {
            self::$distincInstances[md5($file)] = new self($file, false, $description);
        }

        return self::$distincInstances[md5($file)];
    }

    /**
     * @return void
     */
    public static function clean()
    {
        self::$distincInstances = [];
    }

    /**
     * @param ?string $key
     *
     * @return bool
     */
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

    /**
     * @param ?string $key
     * @param ?string $default
     *
     * @return string|null
     */
    public function get($key, $default = null)
    {
        if (empty($this->description)) {
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

    private function findDescription(string $key): ?string
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
