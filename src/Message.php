<?php

namespace Proengeno\Edifact;

use Proengeno\Edifact\Configuration;
use Proengeno\Edifact\Interfaces\SegInterface;
use Proengeno\Edifact\EdifactFile;
use Proengeno\Edifact\SegmentFactory;
use Proengeno\Edifact\Exceptions\EdifactException;
use Proengeno\Edifact\Exceptions\ValidationException;
use Proengeno\Edifact\Exceptions\SegValidationException;

class Message implements \Iterator
{
    protected Configuration $configuration;

    protected EdifactFile $edifactFile;

    protected SegmentFactory $segmentFactory;

    private int|null $pinnedPointer = null;

    private SegInterface|false $currentSegment = false;

    private int $currentSegmentNumber = -1;

    public function __construct(EdifactFile $edifactFile, ?Configuration $configuration = null)
    {
        $this->edifactFile = $edifactFile;
        $this->rewind();

        $this->configuration = $configuration ?: new Configuration;
        $this->segmentFactory = new SegmentFactory(
            $this->configuration->getSegmentNamespace(),
            $this->getDelimiter(),
            $this->configuration->getFallbackSegment()
        );
    }

    public function addStreamFilter(string $filtername, mixed $params = null): self
    {
        $this->edifactFile->addReadFilter($filtername, $params);

        return $this;
    }

    public static function fromFilepath(string $string, Configuration $configuration = null): self
    {
        $edifactFile = new EdifactFile($string);
        $configuration = $configuration ?: new Configuration;

        return new self($edifactFile, $configuration);
    }

    public static function fromString(
        string $string, Configuration $configuration = null, string $filename = 'php://temp'
    ): self
    {
        $configuration = $configuration ?: new Configuration;
        $edifactFile = EdifactFile::fromString($string, $filename);

        return new self($edifactFile, $configuration);
    }

    public function getConfiguration(string $key): mixed
    {
        $method = 'get' . ucfirst($key);
        if (method_exists($this->configuration, $method)) {
            return $this->configuration->$method();
        }

        throw new EdifactException("Unknown Configuration '$key'.");
    }

    public function getFilepath(): string
    {
        return $this->edifactFile->getRealPath();
    }

    public function getCurrentSegment(): SegInterface|false
    {
        if ($this->currentSegment === false) {
            $this->currentSegment = $this->getNextSegment();
        }
        return $this->currentSegment;
    }

    public function getNextSegment(): SegInterface|false
    {
        $segLine = $this->getNextSegLine();

        if ($segLine == false) {
            return false;
        }

        return $this->currentSegment = $this->getSegmentObject($segLine);
    }

    public function findSegmentFromBeginn(string $searchSegment, callable|array|null $criteria = null): SegInterface|false
    {
        $this->rewind();

        return $this->findNextSegment($searchSegment, $criteria);
    }

    public function findNextSegment(string $searchSegment, callable|array|null $criteria = null): SegInterface|false
    {
        while ($segmentObject = $this->getNextSegment()) {
            if ($segmentObject->name() == $searchSegment) {
                if ($this->checkCriteria($criteria, $segmentObject) === true) {
                    return $segmentObject;
                }
                continue;
            }
        }

        return false;
    }

    public function pinPointer(): void
    {
        $this->pinnedPointer = $this->edifactFile->tell();
    }

    public function jumpToPinnedPointer(): int
    {
        if ($this->pinnedPointer === null) {
            return $this->edifactFile->tell();
        }

        $pinnedPointer = $this->pinnedPointer;
        $this->pinnedPointer = null;

        $this->edifactFile->seek($pinnedPointer);

        return $pinnedPointer;
    }

    public function validateSegments(): void
    {
        $this->rewind();

        $segment = false;
        try {
            while ($segment = $this->getNextSegment()) {
                $segment->validate();
            }
        } catch (SegValidationException $e) {
            throw new ValidationException(
                $e->getMessage(), $this->currentSegmentNumber, $segment ? $segment->name() : ''
            );
        }

        $this->rewind();
    }

    public function getDelimiter(): Delimiter
    {
        return $this->edifactFile->getDelimiter();
    }

    public function current(): SegInterface|false
    {
        return $this->getCurrentSegment();
    }

    public function key(): int
    {
        return $this->currentSegmentNumber;
    }

    public function next(): void
    {
        $this->currentSegment = false;
    }

    public function rewind(): void
    {
        $this->edifactFile->rewind();
        $this->currentSegmentNumber = -1;
        $this->currentSegment = false;
    }

    public function valid(): bool
    {
        return $this->current() !== false;
    }

    public function toArray(): array
    {
        return array_map(function($segment) {
            if (! $segment) {
                return [];
            }
            return [$segment->name() => $segment->toArray()];
        }, iterator_to_array($this) ?: []);
    }

    public function __toString(): string
    {
        return $this->edifactFile->__toString();
    }

    protected function getSegmentObject(string $segLine): SegInterface
    {
        return $this->segmentFactory->fromSegline($segLine);
    }

    private function getNextSegLine(): string
    {
        $this->currentSegmentNumber++;

        return $this->edifactFile->getSegment();
    }

    private function checkCriteria(callable|array|null $criteria, SegInterface $segmentObject): bool
    {
        if ($criteria === null) {
            return true;
        }

        if (is_array($criteria)) {
            foreach ($criteria as $getter => $pattern) {
                if ($segmentObject->$getter() != $pattern) {
                    return false;
                }
            }
            return true;
        }

        return $criteria($segmentObject);
    }
}
