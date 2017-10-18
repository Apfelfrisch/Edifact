<?php

namespace Proengeno\Edifact;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\GenericSegment;
use Proengeno\Edifact\Validation\MessageValidator;

class Configuration
{
    protected $genericSegment = GenericSegment::class;
    protected $builder;
    protected $filepath;
    protected $delimiter;
    protected $exportSender;
    protected $unbRefGenerator;
    protected $segmentNamespace;
    protected $readFilter = [];
    protected $writeFilter = [];
    protected $messageDescriptions = [];

    public function setGenericSegment($genericSegment)
    {
        $this->genericSegment = $genericSegment;
    }

    public function getGenericSegment()
    {
        return $this->genericSegment;
    }

    public function addBuilder($key, $class)
    {
        if (!isset($this->builder[$key])) {
            $this->builder[$key] = $class;
        }
    }

    public function getBuilder($key)
    {
        if (isset($this->builder[$key])) {
            return $this->builder[$key];
        }

        return null;
    }

    public function setFilepath($filepath)
    {
        $this->filepath = $filepath;
    }

    public function getFilepath()
    {
        return $this->filepath;
    }

    public function setReadFilter(Callable $callable)
    {
        $this->readFilter[] = $callable;
    }

    public function setWriteFilter(Callable $callable)
    {
        $this->writeFilter[] = $callable;
    }

    public function getReadFilter()
    {
        return $this->readFilter;
    }

    public function getWriteFilter()
    {
        return $this->writeFilter;
    }

    public function setExportSender($exportSender)
    {
        $this->exportSender = $exportSender;
    }

    public function getExportSender()
    {
        return $this->exportSender;
    }

    public function addMessageDescription($descriptionFile, $allocationRules)
    {
        $this->messageDescriptions[$descriptionFile] = $allocationRules;
    }

    public function getMessageDescriptions()
    {
        return $this->messageDescriptions;
    }

    public function setSegmentNamespace($segmentNamespace)
    {
        $this->segmentNamespace = $segmentNamespace;
    }

    public function getSegmentNamespace()
    {
        return $this->segmentNamespace;
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
