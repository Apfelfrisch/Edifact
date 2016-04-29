<?php 

namespace Proengeno\Edifact\Message;

use Exception;
use ReflectionClass;
use Proengeno\Edifact\EdifactFile;

abstract class Builder
{
    private $message;
    private $edifactFile;
    private $unbReference;
    
    public function __construct($message, $from, $to, $mode = 'w+')
    {
        $this->setMessageClass($message);
        $this->edifactFile = new EdifactFile($this->getFilename($from, $to), $mode);
    }

    abstract public function addMessage();

    public function get()
    {
        return $this->edifactFile;
    }

    public function unbReference()
    {
        if (!$this->unbReference) {
            return $this->unbReference = 'UNB_REFERENZ';
        }
        return $this->unbReference;
    }

    private function getFilename($from, $to)
    {
        return $this->messageType . '__' . $from . '_' . $to . '_' . date('Ymd') . '_' . $this->unbReference() . '.txt';
    }
    
    private function setMessageClass($message)
    {
        $class = new ReflectionClass($message);

        while ($parent = $class->getParentClass() ) {
            if ($parent->getName() == Message::class) {
                $this->message = $message;
                return;
            }
            $class = $parent;
        }

        throw new Exception('Class "' . $message . '" not Child of "'. Builder::class . '"');
    }
    
}
