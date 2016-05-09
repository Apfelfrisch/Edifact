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
    private $message;
    private $edifactFile;
    private $unbReference;
    
    public function __construct($message, $from, $to, $type = null, $mode = 'w+')
    {
        $this->setMessageClass($message);
        $this->edifactFile = new EdifactFile($this->getFilename($from, $to), $mode);

        $this->edifactFile->write($this->getUna() . $this->getUnb($from, $to, $type));
    }

    abstract public function addMessage();

    public function get()
    {
        $this->edifactFile->write($this->getUnz());

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
    
    private function getFilename($from, $to)
    {
        return $this->messageType . '__' . $from . '_' . $to . '_' . date('Ymd') . '_' . $this->unbReference() . '.txt';
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
    
    private function getUnb($from, $to, $type)
    {
        return Unb::fromAttributes('UNOC', 3, $from, 500, $to, 500, new DateTime(), $this->unbReference(), $type);
    }

    private function getUnz()
    {
        return Unz::fromAttributes(1, $this->unbReference());
    }
}
