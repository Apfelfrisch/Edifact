<?php

namespace Proengeno\Edifact;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Validation\MessageValidator;
use Proengeno\Edifact\Interfaces\MessageValidatorInterface;

class Configuration
{
    private $delimiter;
    private $messageValidator;
    private $unbRefGenerator;

    public function setMessageValidator(MessageValidatorInterface $messageValidator)
    {
        $this->messageValidator = $messageValidator;
    }

    public function getMessageValidator()
    {
        if (null === $this->messageValidator) {
            $this->messageValidator = new MessageValidator;
        }
        return $this->messageValidator;
    }

    public function setUnbRefGenerator(callable $unbRefGenerator)
    {
        $this->unbRefGenerator = $unbRefGenerator;
    }

    public function getUnbRefGenerator()
    {
        if (null === $this->unbRefGenerator) {
            $this->unbRefGenerator = function() {
                return uniqid();
            };
        }

        return $this->unbRefGenerator;
    }

    public function setDelimiter(Delimiter $delimiter)
    {
        $this->delimiter = $delimiter;
    }

    public function getDelimiter()
    {
        if (null === $this->delimiter) {
            $this->delimiter = new Delimiter;
        }

        return $this->delimiter;
    }
}
