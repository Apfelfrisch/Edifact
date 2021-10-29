<?php

namespace Proengeno\Edifact\Exceptions;

class SegValidationException extends EdifactException
{
    protected string|null $key;

    protected string|null $value;

    /**
     * @param string|null $key
     * @param string|null $value
     * @param string $message
     * @param int $code
     *
     * @return static
     *
     * @psalm-suppress UnsafeInstantiation
     */
    public function __construct($key, $value, $message, $code)
    {
        $this->key = $key;
        $this->value = $value;

        parent::__construct($message, $code);
    }

    public static function forKeyValue(string $key, string $value, string $message, int $code = 0): self
    {
        $message = $key . ' (' . $value . ') : ' . $message;

        return new self($key, $value, $message, $code);
    }

    public static function forKey(string $key, string $message, int $code = 0): self
    {
        $message = $key . ': ' . $message;

        return new self($key, null, $message, $code);
    }

    public static function forValue(string $value, string $message, int $code = 0): self
    {
        $message = $value . ': ' . $message;

        return new self(null, $value, $message, $code);
    }

    public function getKey(): string|null
    {
        return $this->key;
    }

    public function getValue(): string|null
    {
        return $this->value;
    }
}
