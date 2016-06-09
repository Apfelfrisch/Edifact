<?php 

namespace Proengeno\Edifact\Message;

use Closure;
use Iterator;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Validation\MessageValidator;
use Proengeno\Edifact\Exceptions\EdifactException;
use Proengeno\Edifact\Interfaces\EdifactMessageInterface;
use Proengeno\Edifact\Interfaces\MessageValidatorInterface;

abstract class Message implements Iterator, EdifactMessageInterface
{
    protected $configuration = [];

    private $file;
    private $validator;
    private $pinnedPointer;
    private $currentSegment;
    private $segmentBuilder;
    private $currentSegmentNumber = 0;
    
    public function __construct(EdifactFile $file, MessageValidatorInterface $validator = null)
    {
        $this->file = $file;
        $this->validator = $validator ?: new MessageValidator;
        $this->segmentBuilder = new SegmentFactory($this->getDelimiter());
    }
    
    public static function fromString($string)
    {
        $file = new EdifactFile('php://temp', 'w+');
        $file->writeAndRewind($string);
        return new static($file);
    }

    public function addConfiguration($key, Closure $config)
    {
        $this->configuration[$key] = $config;
    }

    public function __toString()
    {
        return $this->file->__toString();
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
        $segment = $this->file->getSegment();

        if ($segment !== false) {
            $segment = $this->currentSegment = $this->getSegmentObject($segment);
        } 
        return $segment;
    }

    public function findNextSegment($searchSegment, $fromStart = false, closure $criteria = null)
    {
        if ($fromStart) {
            $this->rewind();
        }
        
        $searchObject = static::getSegmentClass($searchSegment);
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
    
    public function getValidationBlueprint()
    {
        return static::$validationBlueprint;
    }

    public function validate()
    {
        $this->rewind();
        $this->validator->validate($this);
        $this->rewind();

        return $this;
    }

    public function getDelimiter()
    {
        return $this->file->getDelimiter();
    }

    public static function getSegmentClass($segmentName)
    {
        $segmentName = strtoupper($segmentName);
        if (isset(static::$segments[$segmentName])) {
            return static::$segments[$segmentName];
        }

        throw EdifactException::segmentUnknown($segmentName);
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
    
    protected function getSegmentObject($segLine)
    {
        return $this->segmentBuilder->fromSegline(static::getSegmentClass($this->getSegname($segLine)), $segLine);
    }

    private function getSegname($segLine) 
    {
        return substr($segLine, 0, 3);
    }
}
