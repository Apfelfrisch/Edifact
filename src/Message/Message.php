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

    protected $configuration;
    protected $description;

    private $file;
    private $pinnedPointer;
    private $segmentFactory;
    private $currentSegment;
    private $currentSegmentNumber = 0;

    public function __construct(EdifactFile $file, Describer $description, Configuration $configuration = null)
    {
        $this->edifactFile = $file;
        $this->rewind();
        $this->description = $description;
        $this->configuration = $configuration ?: new Configuration;
        $this->segmentFactory = new SegmentFactory(
            $this->configuration->getSegmentNamespace(),
            $this->getDelimiter()
        );

        foreach ($this->configuration->getStreamFilter(STREAM_FILTER_READ) as $name) {
            $this->edifactFile->appendFilter($name, STREAM_FILTER_READ);
        }
    }

    public static function fromFilepath($string, Configuration $configuration = null)
    {
        $file = new EdifactFile($string);
        $configuration = $configuration ?: new Configuration;
        $description = Describer::build(self::findDescrtiptionFile($file, $configuration));

        return new static($file, $description, $configuration);
    }

    public static function fromString($string, Configuration $configuration = null)
    {
        $file = EdifactFile::fromString($string);
        $configuration = $configuration ?: new Configuration;
        $description = Describer::build(self::findDescrtiptionFile($file, $configuration));

        return new static($file, $description, $configuration);
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

    private function getNextSegLine()
    {
        $this->currentSegmentNumber++;

        return $this->edifactFile->getSegment();
    }

    public function findSegmentFromBeginn($searchSegment, \Closure $criteria = null)
    {
        $this->rewind();

        return $this->findNextSegment($searchSegment, $criteria);
    }

    public function findNextSegment($searchSegment, \Closure $criteria = null)
    {
        $searchObject = $this->segmentFactory->fromSegline($searchSegment);

        while ($segmentObject = $this->getNextSegment()) {
            if ($segmentObject instanceof $searchObject) {
                if ($criteria && !$criteria($segmentObject)) {
                    continue;
                }
                return $segmentObject;
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
        $this->currentSegmentNumber++;
    }

    public function rewind()
    {
        $this->edifactFile->rewind();
        $this->currentSegmentNumber = 0;
        $this->currentSegment = false;
    }

    public function valid()
    {
        return $this->current() !== false;
    }

    public function __toString()
    {
        return $this->edifactFile->__toString();
    }

    protected function getSegmentObject($segLine)
    {
        return $this->segmentFactory->fromSegline($segLine);
    }

    private static function findDescrtiptionFile($file, $configuration)
    {
        $tmpAllocationRules = $configuration->getMessageDescriptions();
        while ($segment = $file->getSegment()) {
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
        throw new EdifactException('Could not find a Description for Message.');
    }

}
