<?php 

namespace Proengeno\Edifact\Message;

use ReflectionClass;
use Proengeno\Edifact\EdifactFile;
use Proengeno\Edifact\Message\Segments\Una;
use Proengeno\Edifact\Message\Segments\Unz;
use Proengeno\Edifact\Message\Segments\Segment;
use Proengeno\Edifact\Exceptions\ValidationException;

abstract class Builder
{
    private $messageCount = 0;
    private $messageWasFetched = false;

    protected $to;
    protected $from;
    protected $edifactFile;
    protected $unhCounter;

    private $message;
    private $unbReference;
    
    public function __construct($message, $from, $to, $mode = 'w+')
    {
        $this->to = $to;
        $this->from = $from;
        $this->setMessageClass($message);
        $this->edifactFile = new EdifactFile($this->getFilename(), $mode);
    }

    public function __destruct()
    {
        $filepath = $this->edifactFile->getRealPath();
        // Datei löschen falls Sie nicht Vollständig erstellt wurde (Exceptions o.ä) 
        if ($this->messageWasFetched === false && file_exists($filepath)) {
            unlink($filepath);
        }
    }

    public function addMessage($message)
    {
        if ($this->messageIsEmpty()) {
            $this->edifactFile->write($this->getUna() . $this->getUnb());
        }
        $this->edifactFile->write($this->getMessage($message));
        $this->messageCount++;

        return $this;
    }

    public function get()
    {
        if (!$this->messageIsEmpty()) {
            $this->edifactFile->write($this->getUnz());
            $this->edifactFile->rewind();
        }
        $this->messageWasFetched = true;
        return new $this->message($this->edifactFile);
    }

    public function unbReference()
    {
        if (!$this->unbReference) {
            return $this->unbReference = uniqid($this->getFirstCharFromMessageClassname());
        }
        return $this->unbReference;
    }
    
    protected function writeSegment(Segment $segment)
    {
        if ($segment instanceof Unh) {
            $this->unhCounter = 1;
        } else {
            $this->unhCounter ++;
        }
        $this->edifactFile->write($segment);
    }
    
    abstract protected function getMessage($array);

    abstract protected function getUnb();

    private function messageIsEmpty()
    {
        return $this->edifactFile->tell() == 0;
    }

    private function setMessageClass($message)
    {
        if (! $this->classesAreRelated($message, Message::class)) {
            throw new ValidationException('Class "' . $message . '" not Child of "'. Builder::class . '"');
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
        return static::MESSAGE_TYPE . '_' . static::MESSAGE_SUBTYPE . '_' . $this->from . '_' . $this->to . '_' . date('Ymd') . '_' . $this->unbReference() . '.txt';
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
    
    private function getUnz()
    {
        return Unz::fromAttributes($this->messageCount, $this->unbReference());
    }
}


