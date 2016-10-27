<?php

namespace Proengeno\Edifact;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Validation\MessageValidator;
use Proengeno\Edifact\Interfaces\MessageValidatorInterface;

class Configuration
{
    protected $delimiter;
    protected $unbRefGenerator;
    protected $messageValidator;
    protected $segmentNamespace;
    protected $importAllocationRules;

    public function setImportAllocationRule($edifactClass, $allocationRules)
    {
        $this->importAllocationRules[$edifactClass] = $allocationRules;
    }

    public function getImportAllocationRule($edifactClass)
    {
        return $this->importAllocationRules[$edifactClass];
    }

    public function getAllImportAllocationRules()
    {
        return $this->importAllocationRules;
    }

    public function setSegmentNamespace($segmentNamespace)
    {
        $this->segmentNamespace = $segmentNamespace;
    }

    public function getSegmentNamespace()
    {
        return $this->segmentNamespace;
    }

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
