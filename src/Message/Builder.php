<?php 

namespace Proengeno\Edifact\Message;

use ReflectionClass;
use Proengeno\Edifact\EdifactFile;
use Proengeno\Edifact\Message\Segment;
use Proengeno\Edifact\Exceptions\ValidationException;

abstract class Builder
{
    protected $edifactFile;
    protected $unhCounter = 0;

    private $edifactClass;
    private $unbReference;
    private $messageCount = 0;
    private $messageWasFetched = false;
    
    public function __construct($edifactClass, $filepath)
    {
        $this->setMessageClass($edifactClass);
        $this->segmentBuilder = new SegmentFactory;
        $this->edifactFile = new EdifactFile($filepath, 'w+');
    }

    public function __destruct()
    {
        // Datei löschen falls Sie nicht Vollständig erstellt wurde (Exceptions o.ä) 
        if ($this->edifactFile) {
            $filepath = $this->edifactFile->getRealPath();
            if ($this->messageWasFetched === false && file_exists($filepath)) {
                unlink($filepath);
            }
        }
    }

    public function addMessage($message)
    {
        if ($this->messageIsEmpty()) {
            $this->writeSeg('una');
            $this->writeUnb();
        }
        $this->writeMessage($message);
        $this->messageCount++;

        return $this;
    }

    public function get()
    {
        if (!$this->messageIsEmpty()) {
            $this->writeSeg('unz', [$this->messageCount, $this->unbReference()]);
            $this->edifactFile->rewind();
        }
        $this->messageWasFetched = true;
        return new $this->edifactClass($this->edifactFile);
    }

    public function unbReference()
    {
        if (!$this->unbReference) {
            return $this->unbReference = uniqid();
        }
        return $this->unbReference;
    }
    
    protected function writeSeg($segment, $attributes = [], $method = 'fromAttributes')
    {
        $edifactClass = $this->edifactClass;
        $segment = $this->segmentBuilder->fromAttributes($edifactClass::getSegmentClass($segment), $attributes, $method);
        $this->edifactFile->write($segment);
        if ($segment->name() == 'UNA' || $segment->name() == 'UNB') {
            return;
        }
        if ($segment->name() == 'UNH') {
            $this->unhCounter = 1;
            return;
        }
        $this->unhCounter ++;
    }
    
    abstract protected function writeUnb();

    abstract protected function writeMessage($array);

    private function messageIsEmpty()
    {
        return $this->edifactFile->tell() == 0;
    }

    private function setMessageClass($edifactClass)
    {
        if (! $this->classesAreRelated($edifactClass, Message::class)) {
            throw new ValidationException('Class "' . $edifactClass . '" is no Child of "'. Builder::class . '"');
        }

        $this->edifactClass = $edifactClass;
    }

    private function classesAreRelated($subclass, $superclass)
    {
        while ($object = (new ReflectionClass($subclass))->getParentClass() ) {
            if ($object->getName() == $superclass) {
                return true;
            }
            $class = $parent;
        }

        return false;
    }
}
