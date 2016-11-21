<?php

namespace Proengeno\Edifact;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Validation\MessageValidator;

class Configuration
{
    protected $filename = 'php://temp';
    protected $builder;
    protected $filepath;
    protected $delimiter;
    protected $exportSender;
    protected $unbRefGenerator;
    protected $segmentNamespace;
    protected $streamFilter = [
        STREAM_FILTER_READ => [],
        STREAM_FILTER_WRITE => [],
    ];
    protected $messageDescriptions = [];

    public function setFilename($filename)
    {
        $this->filename = $filename;
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

    public function setStreamFilter($name, $direction)
    {
        if (in_array($direction, array_keys($this->streamFilter)) && !in_array($name, $this->streamFilter[$direction])) {
            $this->streamFilter[$direction][] = $name;
        }
    }

    public function getStreamFilter($direction)
    {
        if (!in_array($direction, array_keys($this->streamFilter))) {
            return [];
        }
        return $this->streamFilter[$direction];
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
