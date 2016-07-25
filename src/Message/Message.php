<?php

namespace Proengeno\Edifact\Message;

use ReflectionClass;
use Proengeno\Edifact\Interfaces\MessageInterface;

class Message implements MessageInterface
{
    protected $ediMessage;
    
    public function __construct(MessageInterface $ediMessage)
    {
        $this->ediMessage = $ediMessage;
    }

    public function getAdapterName()
    {
        return (new ReflectionClass($this->ediMessage))->getShortName();
    }

    public function getValidationBlueprint()
    {
        return $this->ediMessage->getValidationBlueprint();
    }

    public function addConfiguration($key, $config)
    {
        return $this->ediMessage->addConfiguration($key, $config);
    }

    public function getDelimiter()
    {
        return $this->ediMessage->getDelimiter();
    }

    public function getFilepath()
    {
        return $this->ediMessage->getFilepath();
    }

    public function getCurrentSegment()
    {
        return $this->ediMessage->getCurrentSegment();
    }

    public function getNextSegment()
    {
        return $this->ediMessage->getNextSegment();
    }

    public function findNextSegment($searchSegment)
    {
        return $this->ediMessage->findNextSegment($searchSegment);
    }

    public function pinPointer()
    {
        return $this->ediMessage->pinPointer();
    }

    public function jumpToPinnedPointer()
    {
        return $this->ediMessage->jumpToPinnedPointer();
    }

    public function validate()
    {
        return $this->ediMessage->validate();
    }

    public function validateSegments()
    {
        return $this->ediMessage->validateSegments();
    }

    public function current()
    {
        return $this->ediMessage->current();
    }

    public function key()
    {
        return $this->ediMessage->key();
    }

    public function next()
    {
        return $this->ediMessage->next();
    }

    public function rewind()
    {
        return $this->ediMessage->rewind();
    }

    public function valid()
    {
        return $this->ediMessage->valid();
    }

    public function __toString()
    {
        return $this->ediMessage->__toString();
    }
}
