<?php

namespace Proengeno\Edifact;

use Proengeno\Edifact\Exceptions\EdifactException;
use Proengeno\Edifact\Interfaces\SegInterface;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\GenericSegment;
use Proengeno\Edifact\Interfaces\BuilderInterface;
use Proengeno\Edifact\Validation\MessageValidator;

class Configuration
{
    /** @var class-string<SegInterface> */
    protected $genericSegment = GenericSegment::class;

    /** @var ?array<string, class-string<BuilderInterface>> */
    protected $builder = null;

    /** @var string|null */
    protected $filepath = null;

    /** @var Delimiter|null */
    protected $delimiter = null;

    /** @var string|null */
    protected $exportSender = null;

    /** @var callable|null */
    protected $unbRefGenerator = null;

    /** @var string|null */
    protected $segmentNamespace = null;

    /** @var list<string> */
    protected $readFilter = [];

    /** @var list<string> */
    protected $writeFilter = [];

    /** @var array */
    protected $messageDescriptions = [];

    /**
     * @param class-string<SegInterface> $genericSegment
     *
     * @return void
     */
    public function setGenericSegment($genericSegment)
    {
        $this->genericSegment = $genericSegment;
    }

    /**
     * @return class-string<SegInterface>
     */
    public function getGenericSegment()
    {
        return $this->genericSegment;
    }

    /**
     * @param string $key
     * @param class-string<BuilderInterface> $class
     *
     * @return void
     */
    public function addBuilder($key, $class)
    {
        if (!isset($this->builder[$key])) {
            $this->builder[$key] = $class;
        }
    }

    /**
     * @param string $key
     *
     * @return class-string<BuilderInterface>|null
     */
    public function getBuilder($key)
    {
        if (isset($this->builder[$key])) {
            return $this->builder[$key];
        }

        return null;
    }

    /**
     * @param string $filepath
     *
     * @return void
     */
    public function setFilepath($filepath)
    {
        $this->filepath = $filepath;
    }

    /**
     * @return string|null
     */
    public function getFilepath()
    {
        return $this->filepath;
    }

    /**
     * @return void
     */
    public function setReadFilter(string $filter)
    {
        $this->readFilter[] = $filter;
    }

    /**
     * @return void
     */
    public function setWriteFilter(string $filter)
    {
        $this->writeFilter[] = $filter;
    }

    /**
     * @return list<string>
     */
    public function getReadFilter()
    {
        return $this->readFilter;
    }

    /**
     * @return list<string>
     */
    public function getWriteFilter()
    {
        return $this->writeFilter;
    }

    /**
     * @param string $exportSender
     *
     * @return void
     */
    public function setExportSender($exportSender)
    {
        $this->exportSender = $exportSender;
    }

    /**
     * @return string
     */
    public function getExportSender()
    {
        if ($this->exportSender === null) {
            throw new EdifactException("No exportSender in Configuration available, please set via Configuration::setExportSender ");
        }
        return $this->exportSender;
    }

    /**
     * @param string $descriptionFile
     * @param array $allocationRules
     *
     * @return void
     */
    public function addMessageDescription($descriptionFile, $allocationRules)
    {
        $this->messageDescriptions[$descriptionFile] = $allocationRules;
    }

    /**
     * @return array
     */
    public function getMessageDescriptions()
    {
        return $this->messageDescriptions;
    }

    /**
     * @param string|null $segmentNamespace
     *
     * @return void
     */
    public function setSegmentNamespace($segmentNamespace)
    {
        $this->segmentNamespace = $segmentNamespace;
    }

    /**
     * @return string|null
     */
    public function getSegmentNamespace()
    {
        return $this->segmentNamespace;
    }

    /**
     * @return void
     */
    public function setUnbRefGenerator(callable $unbRefGenerator)
    {
        $this->unbRefGenerator = $unbRefGenerator;
    }

    /**
     * @return Callable
     */
    public function getUnbRefGenerator()
    {
        if (null === $this->unbRefGenerator) {
            $this->unbRefGenerator = fn(): string => uniqid();
        }

        return $this->unbRefGenerator;
    }

    /**
     * @return void
     */
    public function setDelimiter(Delimiter $delimiter)
    {
        $this->delimiter = $delimiter;
    }

    /**
     * @return Delimiter
     */
    public function getDelimiter()
    {
        if (null === $this->delimiter) {
            $this->delimiter = new Delimiter;
        }

        return $this->delimiter;
    }
}
