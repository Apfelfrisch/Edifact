<?php

namespace Proengeno\Edifact\Message;

use Proengeno\Edifact\Configuration;
use Proengeno\Edifact\Message\Describer;
use Proengeno\Edifact\Message\EdifactFile;
use Proengeno\Edifact\Message\SegmentFactory;
use Proengeno\Edifact\Validation\MessageValidator;
use Proengeno\Edifact\Exceptions\EdifactException;
use Proengeno\Edifact\Exceptions\ValidationException;
use Proengeno\Edifact\Exceptions\SegValidationException;

class Message implements \Iterator
{
    protected static $segments;

    /* Proengeno\Edifact\Configuration\Configuration */
    protected $configuration;

    /* Proengeno\Edifact\Message\Describer\Describer */
    protected $description;

    /* Proengeno\Edifact\Message\EdifactFile */
    private $edifactFile;

    /* Proengeno\Edifact\Message\SegmentFactory */
    private $segmentFactory;

    private $pinnedPointer;
    private $currentSegment;
    private $currentSegmentNumber = -1;

    public function __construct(EdifactFile $edifactFile, Configuration $configuration = null, Describer $description = null)
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
        if (isset($this->edifactFile)) {
            unset($this->edifactFile);
        }
    }

    public static function fromFilepath($string, Configuration $configuration = null, Describer $description = null)
    {
        $edifactFile = new EdifactFile($string);
        $configuration = $configuration ?: new Configuration;

        return new static($edifactFile, $configuration, $description);
    }

    public static function fromString($string, Configuration $configuration = null, $filename = 'php://temp', Describer $description = null)
    {
        $configuration = $configuration ?: new Configuration;
        $edifactFile = EdifactFile::fromString($string, $filename, $configuration->getWriteFilter());

        return new static($edifactFile, $configuration, $description);
    }

    public function getConfiguration($key)
    {
        $method = 'get' . ucfirst($key);
        if (method_exists($this->configuration, $method)) {
            return $this->configuration->$method();
        }

        throw new EdifactException("Unknown Configuration '$key'.");
    }

    public function getDescription($key)
    {
        if ($this->description === null) {
            $this->description = Describer::build(self::findDescrtiptionFile($this->edifactFile, $this->configuration));
        }
        return $this->description->get($key);
    }

    public function getFilepath()
    {
        return $this->edifactFile->getRealPath();
    }

    public function getCurrentSegment()
    {
        if ($this->currentSegment === false) {
            $this->currentSegment = $this->getNextSegment();
        }
        return $this->currentSegment;
    }

    public function getNextSegment()
    {
        $segLine = $this->getNextSegLine();

        if ($segLine == false) {
            return false;
        }

        return $this->currentSegment = $this->getSegmentObject($segLine);
    }

    public function findSegmentFromBeginn($searchSegment, $criteria = null)
    {
        $this->rewind();

        return $this->findNextSegment($searchSegment, $criteria);
    }

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

    public function pinPointer()
    {
        $this->pinnedPointer = $this->edifactFile->tell();
    }

    public function jumpToPinnedPointer()
    {
        if ($this->pinnedPointer === null) {
            return $this->edifactFile->tell();
        }

        $pinnedPointer = $this->pinnedPointer;
        $this->pinnedPointer = null;

        return $this->edifactFile->seek($pinnedPointer);
    }

    public function validate(MessageValidator $validator = null)
    {
        $validator = $validator ?: new MessageValidator;
        $validator->validate($this);

        return $this;
    }

    public function validateSegments()
    {
        $this->rewind();
        try {
            while ($segment = $this->getNextSegment()) {
                $segment->validate();
            }
        } catch (SegValidationException $e) {
            throw new ValidationException(
                $e->getMessage(), $this->currentSegmentNumber, $segment->name()
            );
        }
        $this->rewind();
    }

    public function getDelimiter()
    {
        return $this->edifactFile->getDelimiter();
    }

    public function current()
    {
        return $this->getCurrentSegment();
    }

    public function key()
    {
        return $this->currentSegmentNumber;
    }

    public function next()
    {
        $this->currentSegment = false;
    }

    public function rewind()
    {
        $this->edifactFile->rewind();
        $this->currentSegmentNumber = -1;
        $this->currentSegment = false;
    }

    public function valid()
    {
        return $this->current() !== false;
    }

    public function toArray()
    {
        return array_map(function($segment) {
            return [$segment->name() => $segment->toArray()];
        }, iterator_to_array($this));
    }

    public function __toString()
    {
        return $this->edifactFile->__toString();
    }

    protected function getSegmentObject($segLine)
    {
        return $this->segmentFactory->fromSegline($segLine, null, $this->configuration->getGenericSegment());
    }

    private function getNextSegLine()
    {
        $this->currentSegmentNumber++;

        return $this->edifactFile->getSegment();
    }

    private static function findDescrtiptionFile($edifactFile, $configuration)
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
    }

    private function checkCriteria($criteria, $segmentObject)
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
