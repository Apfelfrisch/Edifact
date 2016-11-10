<?php

namespace Proengeno\Edifact\Templates;

use Closure;
use Proengeno\Edifact\Configuration;
use Proengeno\Edifact\Message\EdifactFile;
use Proengeno\Edifact\Message\SegmentFactory;
use Proengeno\Edifact\Validation\MessageValidator;
use Proengeno\Edifact\Exceptions\EdifactException;
use Proengeno\Edifact\Interfaces\MessageInterface;
use Proengeno\Edifact\Exceptions\ValidationException;
use Proengeno\Edifact\Exceptions\SegValidationException;
use Proengeno\Edifact\Interfaces\MessageValidatorInterface;

abstract class AbstractMessage implements MessageInterface
{
    protected static $segments;
    protected static $blueprint;

    protected $configuration;

    private $file;
    private $pinnedPointer;
    private $currentSegment;
    private $segmentFactory;
    private $currentSegmentNumber = 0;

    public function __construct(EdifactFile $file, Configuration $configuration = null)
    {
        $this->file = $file;
        $this->configuration = $configuration ?: new Configuration;
        $this->segmentFactory = new SegmentFactory(
            $this->configuration->getSegmentNamespace(),
            $this->getDelimiter()
        );
    }

    public static function fromFilepath($string, Configuration $configuration = null)
    {
        return new static(new EdifactFile($string), $configuration);
    }

    public static function fromString($string, Configuration $configuration = null)
    {
        return new static(EdifactFile::fromString($string), $configuration);
    }

    public function getConfiguration($key)
    {
        $method = 'get' . ucfirst($key);
        if (method_exists($this->configuration, $method)) {
            return $this->configuration->$method();
        }

        throw new EdifactException("Unknown Configuration '$key'.");
    }

    public function getValidationBlueprint()
    {
        if (static::$blueprint !== null) {
            return static::$blueprint;
        }
    }

    public function getFilepath()
    {
        return $this->file->getRealPath();
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
        $this->currentSegmentNumber++;
        $segLine = $this->file->getSegment();

        if ($segLine == false) {
            return false;
        }

        return $this->currentSegment = $this->getSegmentObject($segLine);
    }

    public function findSegmentFromBeginn($searchSegment, closure $criteria = null)
    {
        $this->rewind();

        return $this->findNextSegment($searchSegment, $criteria);
    }

    public function findNextSegment($searchSegment, closure $criteria = null)
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
        $this->pinnedPointer = $this->file->tell();
    }

    public function jumpToPinnedPointer()
    {
        if ($this->pinnedPointer === null) {
            return $this->file->tell();
        }

        $pinnedPointer = $this->pinnedPointer;
        $this->pinnedPointer = null;

        return $this->file->seek($pinnedPointer);
    }

    public function validate()
    {
        $this->rewind();
        $this->configuration->getMessageValidator()->validate($this);
        $this->rewind();

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
    }

    public function getDelimiter()
    {
        return $this->file->getDelimiter();
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
        $this->file->rewind();
        $this->currentSegmentNumber = 0;
        $this->currentSegment = false;
    }

    public function valid()
    {
        return $this->current() !== false;
    }

    public function __toString()
    {
        return $this->file->__toString();
    }

    protected function getSegmentObject($segLine)
    {
        return $this->segmentFactory->fromSegline($segLine);
    }
}
