<?php 

namespace Proengeno\Edifact\Message;

use DateTime;
use Exception;
use ReflectionClass;
use Proengeno\Edifact\EdifactFile;
use Proengeno\Edifact\Message\Segments\Una;
use Proengeno\Edifact\Message\Segments\Unb;
use Proengeno\Edifact\Message\Segments\Unz;

abstract class Builder
{
    private $messageCount = 0;

    private $to;
    private $from;
    private $type;
    private $message;
    private $edifactFile;
    private $unbReference;
    
    public function __construct($message, $from, $to, $type = null, $mode = 'w+')
    {
        $this->to = $to;
        $this->from = $from;
        $this->type = $type;
        $this->setMessageClass($message);
        $this->edifactFile = new EdifactFile($this->getFilename(), $mode);
    }

    public function addMessage(array $array)
    {
        if ($this->messageIsEmpty()) {
            $this->edifactFile->write($this->getUna() . $this->getUnb());
        }
        $this->edifactFile->write($this->getMessage($array));
        $this->messageCount++;

        return $this;
    }

    private function messageIsEmpty()
    {
        return $this->edifactFile->tell() == 0;
    }

    abstract protected function getMessage($array);
    
    public function get()
    {
        if (!$this->messageIsEmpty()) {
            $this->edifactFile->write($this->getUnz());
            $this->edifactFile->rewind();
        }

        return new $this->message($this->edifactFile);
    }

    public function unbReference()
    {
        if (!$this->unbReference) {
            return $this->unbReference = uniqid($this->getFirstCharFromMessageClassname());
        }
        return $this->unbReference;
    }

    private function setMessageClass($message)
    {
        if (! $this->classesAreRelated($message, Message::class)) {
            throw new Exception('Class "' . $message . '" not Child of "'. Builder::class . '"');
        }

        $this->message = $message;
    }

    private function classesAreRelated($subclass, $superclass)
    {
        while ($object = $this->reflectMessageClass($subclass)->getParentClass() ) {
            if ($object->getName() == $superclass) {
                return true;
            }
            $class = $parent;
        }

        return false;
    }
    
    private function getFilename()
    {
        return $this->messageType . '_' . $this->type . '_' . $this->from . '_' . $this->to . '_' . date('Ymd') . '_' . $this->unbReference() . '.txt';
    }
    
    private function getFirstCharFromMessageClassname()
    {
        return $this->reflectMessageClass()->getShortName()[0];
    }

    private function reflectMessageClass($class = null)
    {
        return new ReflectionClass($class ?: $this->message);
    }

    private function getUna()
    {
        return Una::fromAttributes();
    }
    
    private function getUnb()
    {
        return Unb::fromAttributes('UNOC', 3, $this->from, 500, $this->to, 500, new DateTime(), $this->unbReference(), $this->type);
    }

    private function getUnz()
    {
        return Unz::fromAttributes($this->messageCount, $this->unbReference());
    }
}
