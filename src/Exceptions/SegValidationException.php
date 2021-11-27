<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Exceptions;

class SegValidationException extends EdifactException
{
    private ?string $key;
    private ?string $value;

    public function __construct(?string $key, ?string $value, string $message, int $code)
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

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }
}
