<?php 

namespace Proengeno\Edifact\Message;

use Proengeno\Edifact\EdifactFile;
use Proengeno\Edifact\EdifactRegistrar;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Validation\MessageValidator;
use Proengeno\Edifact\Interfaces\EdifactMessageInterface;
use Proengeno\Edifact\Interfaces\MessageValidatorInterface;

abstract class MessageCore implements EdifactMessageInterface
{
    protected static $validationBlueprint;

    private $file;
    private $delimter;
    private $validator;
    
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
    
    public function getNextSegment()
    {
        $segment = $this->file->streamGetSegment();

        if ($segment !== false) {
            return $this->getSegmentObject($segment);
        }
        return false;
    }
    
    public function getPointerPosition()
    {
        return $this->file->tell();
    }

    public function setPointerPosition($position)
    {
        return $this->file->seek($position);
    }

    public function getSegments()
    {
        //return $this->file;
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
        if ($this->delimter !== null) {
            $this->delimter = new Delimiter;
        }
        return $this->delimter;
    }

    private function getSegmentObject($segLine)
    {
        return call_user_func_array(EdifactRegistrar::getSegment($this->getSegname($segLine)) . '::fromSegLine', [$segLine, $this->getDelimter()]);
    }

    private function getSegname($segLine) 
    {
        return substr($segLine, 0, 3);
    }
}
