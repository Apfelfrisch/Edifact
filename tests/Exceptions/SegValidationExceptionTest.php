<?php

namespace Proengeno\Edifact\Test\Exceptions;

use Proengeno\Edifact\Test\TestCase;
use Proengeno\Edifact\Exceptions\SegValidationException;

class SegValidationExceptionTest extends TestCase 
{
    private $key;
    private $value;
    private $code;

    public function setUp()
    {
        $this->key = 'key';
        $this->value = 'value';
        $this->code = 15;
    }
    
    /** @test */
    public function it_can_instanciate_over_key_value_named_constructor()
    {
        $givenMessage = 'Message';
        $expectedMessage = $this->key . ' (' . $this->value . ') : ' . $givenMessage;

        $exception = SegValidationException::forKeyValue($this->key, $this->value, $givenMessage, $this->code);

        $this->assertEquals($this->key, $exception->getKey());
        $this->assertEquals($this->value, $exception->getValue());
        $this->assertEquals($expectedMessage, $exception->getMessage());
        $this->assertEquals($this->code, $exception->getCode());
    }

    /** @test */
    public function it_can_instanciate_over_key_named_constructor()
    {
        $givenMessage = 'Message';
        $expectedMessage = $this->key . ': ' . $givenMessage;
        $expectedValue = null;

        $exception = SegValidationException::forKey($this->key, $givenMessage, $this->code);

        $this->assertEquals($this->key, $exception->getKey());
        $this->assertEquals($expectedValue , $exception->getValue());
        $this->assertEquals($expectedMessage, $exception->getMessage());
        $this->assertEquals($this->code, $exception->getCode());
    }

    /** @test */
    public function it_can_instanciate_over_value_named_constructor()
    {
        $givenMessage = 'Message';
        $expectedMessage = $this->value . ': ' . $givenMessage;
        $expectedKey = null;

        $exception = SegValidationException::forValue($this->value, $givenMessage, $this->code);

        $this->assertEquals($expectedKey, $exception->getKey());
        $this->assertEquals($this->value, $exception->getValue());
        $this->assertEquals($expectedMessage, $exception->getMessage());
        $this->assertEquals($this->code, $exception->getCode());
    }
}
    
