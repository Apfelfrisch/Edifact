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
    /** @var Configuration */
    protected $configuration;

    /** @var Describer|null */
    protected $description;

    /** @var EdifactFile */
    private $edifactFile;

    /** @var SegmentFactory */
    private $segmentFactory;

    /** @var int|null */
    private $pinnedPointer = null;

    /** @var SegInterface|false */
    private $currentSegment = false;

    /** @var int */
    private $currentSegmentNumber = -1;

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

    public function __destruct()
    {
        unset($this->edifactFile);
    }

    /**
     * @param string $string
     *
     * @return static
     *
     * @psalm-suppress UnsafeInstantiation
     */
    public static function fromFilepath($string, Configuration $configuration = null, Describer $description = null)
    {
        $edifactFile = new EdifactFile($string);
        $configuration = $configuration ?: new Configuration;

        return new static($edifactFile, $configuration, $description);
    }

    /**
     * @param string $string
     * @param string $filename
     *
     * @return static
     *
     * @psalm-suppress UnsafeInstantiation
     */
    public static function fromString($string, Configuration $configuration = null, $filename = 'php://temp', Describer $description = null)
    {
        $configuration = $configuration ?: new Configuration;
        $edifactFile = EdifactFile::fromString($string, $filename, $configuration->getWriteFilter());

        return new static($edifactFile, $configuration, $description);
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getConfiguration($key)
    {
        $method = 'get' . ucfirst($key);
        if (method_exists($this->configuration, $method)) {
            return $this->configuration->$method();
        }

        throw new EdifactException("Unknown Configuration '$key'.");
    }

    /**
     * @param string $key
     *
     * @return string|array|null
     */
    public function getDescription($key)
    {
        if ($this->description === null) {
            $this->description = Describer::build(self::findDescrtiptionFile($this->edifactFile, $this->configuration));
        }
        return $this->description->get($key);
    }

    /**
     * @return void
     */
    public function closeStream()
    {
        $this->edifactFile->close();
    }

    /**
     * @return string
     */
    public function getFilepath()
    {
        return $this->edifactFile->getRealPath();
    }

    /**
     * @return SegInterface|false
     */
    public function getCurrentSegment()
    {
        if ($this->currentSegment === false) {
            $this->currentSegment = $this->getNextSegment();
        }
        return $this->currentSegment;
    }

    /**
     * @return SegInterface|false
     */
    public function getNextSegment()
    {
        $segLine = $this->getNextSegLine();

        if ($segLine == false) {
            return false;
        }

        return $this->currentSegment = $this->getSegmentObject($segLine);
    }

    /**
     * @param string $searchSegment
     * @param callable|array|null $criteria
     *
     * @return SegInterface|false
     */
    public function findSegmentFromBeginn($searchSegment, $criteria = null)
    {
        $this->rewind();

        return $this->findNextSegment($searchSegment, $criteria);
    }

    /**
     * @param string $searchSegment
     * @param callable|array|null $criteria
     *
     * @return SegInterface|false
     */
    public function findNextSegment($searchSegment, $criteria = null)
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

    /**
     * @return void
     */
    public function pinPointer()
    {
        $this->pinnedPointer = $this->edifactFile->tell();
    }

    /**
     * @return int
     */
    public function jumpToPinnedPointer()
    {
        if ($this->pinnedPointer === null) {
            return $this->edifactFile->tell();
        }

        $pinnedPointer = $this->pinnedPointer;
        $this->pinnedPointer = null;

        $this->edifactFile->seek($pinnedPointer);

        return $pinnedPointer;
    }

    /**
     * @return static
     */
    public function validate(MessageValidator $validator = null)
    {
        $validator = $validator ?: new MessageValidator;
        $validator->validate($this);

        return $this;
    }

    /**
     * @return void
     */
    public function validateSegments()
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

    /**
     * @return Delimiter
     */
    public function getDelimiter()
    {
        return $this->edifactFile->getDelimiter();
    }

    /**
     * @return SegInterface|mixed
     */
    public function current()
    {
        return $this->getCurrentSegment();
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->currentSegmentNumber;
    }

    /**
     * @return void
     */
    public function next()
    {
        $this->currentSegment = false;
    }

    /**
     * @return void
     */
    public function rewind()
    {
        $this->edifactFile->rewind();
        $this->currentSegmentNumber = -1;
        $this->currentSegment = false;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->current() !== false;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_map(function($segment) {
            return [$segment->name() => $segment->toArray()];
        }, iterator_to_array($this) ?: []);
    }

    /**
     * @return string
     */
    public function __toString()
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
