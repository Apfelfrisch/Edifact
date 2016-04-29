<?php 

namespace Proengeno\Edifact\Message;

use Proengeno\Edifact\EdifactFile;
use Proengeno\Edifact\EdifactRegistrar;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Validation\MessageValidator;
use Proengeno\Edifact\Interfaces\EdifactMessageInterface;
use Proengeno\Edifact\Interfaces\MessageValidatorInterface;

abstract class Message implements EdifactMessageInterface
{
    const UNA_SEGMENT = 'UNA';

    protected static $validationBlueprint;
    
    private $file;
    private $delimter;
    private $validator;
    private $currentSegment;
    
    public function __construct(EdifactFile $file, MessageValidatorInterface $validator = null)
    {
        $this->file = $file;
        $this->validator = $validator ?: new MessageValidator;
    }
    
    public static function fromString($string)
    {
        $file = new EdifactFile('php://memory', 'wb+');
        $file->write($string);
        $file->rewind();

        return new static($file);
    }
    
    public function __toString()
    {
        return $this->file->__toString();
    }

    public function getCurrentSegment()
    {
        return $this->currentSegment;
    }
    
    public function getNextSegment()
    {
        $segment = $this->file->streamGetSegment($this->getDelimter()->getSegment());

        if ($segment !== false) {
            $segment = $this->currentSegment = $this->getSegmentObject($segment);
        } 
        return $segment;
    }
    
    public function getPointerPosition()
    {
        return $this->file->tell();
    }

    public function setPointerPosition($position)
    {
        return $this->file->seek($position);
    }

    public function getValidationBlueprint()
    {
        return static::$validationBlueprint;
    }

    public function findSegments($segmentSearch, $messageCount = null, $bodyCount = null)
    {
        //
    }

    public function validate()
    {
        $this->validator->validate($this);

        return $this;
    }

    public function getDelimter()
    {
        if ($this->delimter === null) {
            $position = $this->getPointerPosition();
            $this->file->rewind();

            if ($this->file->read(3) != self::UNA_SEGMENT) {
                $this->delimter = new Delimiter();
            } else {
                $this->delimter = new Delimiter(
                    $this->file->read(1),
                    $this->file->read(1),
                    $this->file->read(1),
                    $this->file->read(1),
                    $this->file->read(1),
                    $this->file->read(1)
                );
            }
            $this->setPointerPosition($position);
        }
        return $this->delimter;
    }

    abstract public static function build($from, $to);

    private function getSegmentObject($segLine)
    {
        return call_user_func_array(EdifactRegistrar::getSegment($this->getSegname($segLine)) . '::fromSegLine', [$segLine, $this->getDelimter()]);
    }

    private function getSegname($segLine) 
    {
        return substr($segLine, 0, 3);
    }
}
