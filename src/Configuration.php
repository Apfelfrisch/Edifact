<?php

namespace Proengeno\Edifact;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Validation\MessageValidator;
use Proengeno\Edifact\Interfaces\MessageValidatorInterface;

class Configuration
{
    protected $filename = 'php://temp';
    protected $filepath;
    protected $delimiter;
    protected $exportSender;
    protected $unbRefGenerator;
    protected $messageValidator;
    protected $segmentNamespace;
    protected $importAllocationRules;

    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function setFilepath($filepath)
    {
        $this->filepath = $filepath;
    }

    public function getFilepath()
    {
        return $this->filepath;
    }

    public function setExportSender($exportSender)
    {
        $this->exportSender = $exportSender;
    }

    public function getExportSender()
    {
        return $this->exportSender;
    }

    public function setImportAllocationRule(array $allocationRules)
    {
        $this->importAllocationRules = $allocationRules;
    }

    public function addImportAllocationRule($edifactClass, $allocationRules)
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
