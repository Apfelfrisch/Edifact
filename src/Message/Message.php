<?php

namespace Proengeno\Edifact\Message;

use Proengeno\Edifact\Configuration;
use Proengeno\Edifact\Interfaces\SegInterface;
use Proengeno\Edifact\Message\Describer;
use Proengeno\Edifact\Message\EdifactFile;
use Proengeno\Edifact\Message\SegmentFactory;
use Proengeno\Edifact\Validation\MessageValidator;
use Proengeno\Edifact\Exceptions\EdifactException;
use Proengeno\Edifact\Exceptions\ValidationException;
use Proengeno\Edifact\Exceptions\SegValidationException;

class Message implements \Iterator
{
    protected Configuration $configuration;

    protected Describer|null $description;

    private EdifactFile $edifactFile;

    private SegmentFactory $segmentFactory;

    private int|null $pinnedPointer = null;

    private SegInterface|false $currentSegment = false;

    private int $currentSegmentNumber = -1;

    public function __construct(EdifactFile $edifactFile, ?Configuration $configuration = null, ?Describer $description = null)
    {
        $this->edifactFile = $edifactFile;
        $this->rewind();

        $this->description = $description;
        $this->configuration = $configuration ?: new Configuration;
        $this->segmentFactory = new SegmentFactory(
            $this->configuration->getSegmentNamespace(),
            $this->getDelimiter(),
            $this->configuration->getGenericSegment()
        );

        foreach ($this->configuration->getReadFilter() as $callable) {
            $this->edifactFile->addReadFilter($callable);
        }
    }

    public static function fromFilepath(string $string, Configuration $configuration = null, Describer $description = null): self
    {
        $edifactFile = new EdifactFile($string);
        $configuration = $configuration ?: new Configuration;

        return new self($edifactFile, $configuration, $description);
    }

    public static function fromString(
        string $string, Configuration $configuration = null, string $filename = 'php://temp', Describer $description = null
    ): self
    {
        $configuration = $configuration ?: new Configuration;
        $edifactFile = EdifactFile::fromString($string, $filename, $configuration->getWriteFilter());

        return new self($edifactFile, $configuration, $description);
    }

    public function getConfiguration(string $key): mixed
    {
        $method = 'get' . ucfirst($key);
        if (method_exists($this->configuration, $method)) {
            return $this->configuration->$method();
        }

        throw new EdifactException("Unknown Configuration '$key'.");
    }

    public function getDescription(string $key): string|array|null
    {
        if ($this->description === null) {
            $this->description = Describer::build(self::findDescrtiptionFile($this->edifactFile, $this->configuration));
        }
        return $this->description->get($key);
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

    public function validate(MessageValidator $validator = null): self
    {
        $validator = $validator ?: new MessageValidator;
        $validator->validate($this);

        return $this;
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

    private static function findDescrtiptionFile(EdifactFile $edifactFile, Configuration $configuration): string
    {
        $tmpAllocationRules = $configuration->getMessageDescriptions();
        while ($segment = $edifactFile->getSegment()) {
            $segmenName = substr($segment, 0, 3);
            foreach ($tmpAllocationRules as $descriptionFile => $allocationRules) {
                if (isset($allocationRules[$segmenName]) && preg_match($allocationRules[$segmenName], $segment)) {
                    unset($tmpAllocationRules[$descriptionFile][$segmenName]);
                    if (count($tmpAllocationRules[$descriptionFile]) == 0) {
                        return $descriptionFile;
                    }
                }
            }
        }

        throw EdifactException::messageUnknown($edifactFile->getFilename());
    }

    private function checkCriteria(callable|array|null $criteria, SegInterface $segmentObject): bool
    {
        if ($criteria == null) {
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
