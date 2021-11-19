<?php

namespace Proengeno\Edifact;

use Closure;
use Proengeno\Edifact\Interfaces\SegInterface;
use Proengeno\Edifact\Delimiter;
use Proengeno\Edifact\Segments\Generic;

class Configuration
{
    /** @var class-string<SegInterface>|null */
    protected ?string $genericSegment = Generic::class;

    protected ?string $segmentNamespace = 'Proengeno\Edifact\Segments';

    protected Delimiter $delimiter;

    protected Closure $unbRefGenerator;

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
