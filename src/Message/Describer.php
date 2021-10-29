<?php

namespace Proengeno\Edifact\Message;

use Proengeno\Edifact\Exceptions\ValidationException;

class Describer
{
    private static array $distincInstances = [];
    private array $description = [];
    private bool $throwException;
    private ?string $defaultDescription;

    private function __construct(string $file = null, bool $throwException = true, ?string $defaultDescription = null)
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

    public static function build(string $file): self
    {
        if (!isset(self::$distincInstances[md5($file)])) {
            self::$distincInstances[md5($file)] = new self($file);
        }

        return self::$distincInstances[md5($file)];
    }

    public static function buildWithDefaultDescription(string $file, ?string $description = null): self
    {
        if (!isset(self::$distincInstances[md5($file)])) {
            self::$distincInstances[md5($file)] = new self($file, false, $description);
        }

        return self::$distincInstances[md5($file)];
    }

    public static function clean(): void
    {
        self::$distincInstances = [];
    }

    public function has(?string $key): bool
    {
        if ($key === null) {
            return false;
        }

        if (array_key_exists($key, $this->description)) {
            return true;
        }

        return !! $this->findDescription($key);
    }

    public function get(?string $key, ?string $default = null): string|array|null
    {
        if (empty($this->description)) {
            throw new ValidationException('No Description set.');
        }

        if ($key === null) {
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
