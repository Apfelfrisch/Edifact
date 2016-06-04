<?php 

namespace Proengeno\Edifact\Exceptions;

class SegValidationException extends EdifactException
{
    protected $key;
    protected $value;

    public function __construct($key, $value, $message, $code)
    {
        $this->setKey($key);
        $this->setValue($value);
        parent::__construct($message, $code);
    }
    
    public static function forKeyValue($key, $value, $message, $code = 0)
    {
        $message = $key . ' (' . $value . '): ' . $message;

        return new static($key, $value, $message, $code);
    }

    public static function forKey($key, $message, $code = 0)
    {
        $message = $key . ': ' . $message;

        return new static($key, null, $message , $code);
    }

    public static function forValue($value, $message, $code = 0)
    {
        $message = $value . ': ' . $message;

        return new static(null, $value, $message, $code);
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getValue()
    {
        return $this->value;
    }

    private function setKey($key)
    {
        return $this->key = $key;
    }

    private function setValue($value)
    {
        return $this->value = $value;
    }
}
