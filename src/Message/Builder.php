<?php 

namespace Proengeno\Edifact\Message;

use ReflectionClass;
use Proengeno\Edifact\EdifactFile;
use Proengeno\Edifact\Message\Segment;
use Proengeno\Edifact\EdifactRegistrar;
use Proengeno\Edifact\Exceptions\ValidationException;

abstract class Builder
{
    protected $to;
    protected $from;
    protected $edifactFile;
    protected $unhCounter = 0;

    private $message;
    private $unbReference;
    private $messageCount = 0;
    private $messageWasFetched = false;
    
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
            $this->writeSegment($this->getSeg('una')::fromAttributes());
            $this->writeSegment($this->getUnb());
        }
        $this->writeMessage($message);
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
        $this->edifactFile->write($segment);
        if (is_a($segment, $this->getSeg('una')) || is_a($segment, $this->getSeg('unb')) ) {
            return;
        }
        if (is_a($segment, $this->getSeg('unh')) ) {
            $this->unhCounter = 1;
            return;
        }
        $this->unhCounter ++;
    }
    
    abstract protected function writeMessage($array);

    abstract protected function getUnb();

    protected function getSeg($seg)
    {
        return EdifactRegistrar::getSegment($seg);
    }

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
        return $this->getSeg('una')::fromAttributes();
    }
    
    private function getUnz()
    {
        return $this->getSeg('unz')::fromAttributes($this->messageCount, $this->unbReference());
    }
}



