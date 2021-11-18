<?php

namespace Proengeno\Edifact;

use Closure;
use Proengeno\Edifact\Exceptions\EdifactException;
use Proengeno\Edifact\Interfaces\SegInterface;
use Proengeno\Edifact\Delimiter;
use Proengeno\Edifact\Segments\Generic;
use Proengeno\Edifact\Interfaces\BuilderInterface;

class Configuration
{
    /** @var class-string<SegInterface> */
    protected ?string $genericSegment = Generic::class;

    protected ?string $segmentNamespace = 'Proengeno\Edifact\Segments';

    protected Delimiter $delimiter;

    protected Closure $unbRefGenerator;

    /** @var ?array<string, class-string<BuilderInterface>> */
    protected ?array $builder = null;

    protected ?string $filepath = null;

    protected ?string $exportSender = null;

    /** @var list<string> */
    protected array $readFilter = [];

    /** @var list<string> */
    protected array $writeFilter = [];

    protected array $messageDescriptions = [];

    public function __construct()
    {
        $this->delimiter = new Delimiter;
        $this->unbRefGenerator = static fn(): string => uniqid();
    }

    /**
     * @param class-string<SegInterface>|null $genericSegment
     */
    public function setGenericSegment(?string $genericSegment): void
    {
        $this->genericSegment = $genericSegment;
    }

    /**
     * @return class-string<SegInterface>|null
     */
    public function getGenericSegment(): ?string
    {
        return $this->genericSegment;
    }

    /**
     * @param class-string<BuilderInterface> $class
     */
    public function addBuilder(string $key, string $class): void
    {
        if (!isset($this->builder[$key])) {
            $this->builder[$key] = $class;
        }
    }

    /**
     * @return class-string<BuilderInterface>|null
     */
    public function getBuilder(string $key): ?string
    {
        if (isset($this->builder[$key])) {
            return $this->builder[$key];
        }

        return null;
    }

    public function setFilepath(string $filepath): void
    {
        $this->filepath = $filepath;
    }

    public function getFilepath(): ?string
    {
        return $this->filepath;
    }

    public function setReadFilter(string $filter): void
    {
        $this->readFilter[] = $filter;
    }

    public function setWriteFilter(string $filter): void
    {
        $this->writeFilter[] = $filter;
    }

    /**
     * @return list<string>
     */
    public function getReadFilter(): array
    {
        return $this->readFilter;
    }

    /**
     * @return list<string>
     */
    public function getWriteFilter(): array
    {
        return $this->writeFilter;
    }

    public function setExportSender(string $exportSender): void
    {
        $this->exportSender = $exportSender;
    }

    public function getExportSender(): string
    {
        if ($this->exportSender === null) {
            throw new EdifactException("No exportSender in Configuration available, please set via Configuration::setExportSender ");
        }
        return $this->exportSender;
    }

    public function addMessageDescription(string $descriptionFile, array $allocationRules): void
    {
        $this->messageDescriptions[$descriptionFile] = $allocationRules;
    }

    public function getMessageDescriptions(): array
    {
        return $this->messageDescriptions;
    }

    public function setSegmentNamespace(?string $segmentNamespace): void
    {
        $this->segmentNamespace = $segmentNamespace;
    }

    public function getSegmentNamespace(): ?string
    {
        return $this->segmentNamespace;
    }

    public function setUnbRefGenerator(Closure $unbRefGenerator): void
    {
        $this->unbRefGenerator = $unbRefGenerator;
    }

    public function getUnbRefGenerator(): Closure
    {
        return $this->unbRefGenerator;
    }

    public function setDelimiter(Delimiter $delimiter): void
    {
        $this->delimiter = $delimiter;
    }

    public function getDelimiter(): Delimiter
    {
        return $this->delimiter;
    }
}
