<?php

namespace Proengeno\Edifact\Exceptions;

class SegValidationException extends EdifactException
{
    /** @var string|null $key */
    protected $key;

    /** @var string|null $key */
    protected $value;

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

    /**
     * @param string $key
     * @param string $value
     * @param string $message
     * @param int $code
     *
     * @return static
     *
     * @psalm-suppress UnsafeInstantiation
     */
    public static function forKeyValue($key, $value, $message, $code = 0)
    {
        $message = $key . ' (' . $value . ') : ' . $message;

        return new static($key, $value, $message, $code);
    }

    /**
     * @param string $key
     * @param string $message
     * @param int $code
     *
     * @return static
     *
     * @psalm-suppress UnsafeInstantiation
     */
    public static function forKey($key, $message, $code = 0)
    {
        $message = $key . ': ' . $message;

        return new static($key, null, $message, $code);
    }

    /**
     * @param string $value
     * @param string $message
     * @param int $code
     *
     * @return static
     *
     * @psalm-suppress UnsafeInstantiation
     */
    public static function forValue($value, $message, $code = 0)
    {
        $message = $value . ': ' . $message;

        return new static(null, $value, $message, $code);
    }

    /**
     * @return string|null
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }
}
