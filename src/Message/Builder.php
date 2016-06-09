<?php 

namespace Proengeno\Edifact\Message;

use Closure;
use ReflectionClass;
use Proengeno\Edifact\Message\Segment;
use Proengeno\Edifact\Exceptions\ValidationException;

abstract class Builder
{
    protected $to;
    protected $from;
    protected $edifactFile;
    protected $prebuildConfig = [];
    protected $postbuildConfig = [];

    private $edifactClass;
    private $unbReference;
    private $segmentBuilder;
    
    private $unhCounter = 0;
    private $messageCount = 0;
    private $messageWasFetched = false;
    
    public function __construct($from, $to, $filepath = null)
    {
        $this->to = $to;
        $this->from = $from;
        $this->segmentBuilder = new SegmentFactory;
        $this->edifactClass = $this->getMessageClass();
        $this->edifactFile = new EdifactFile($filepath ?: 'php://temp', 'w+');
        $this->prebuildConfig['unbReference'] = function() { 
            return uniqid();
        };
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

    public function addPrebuildConfig($key, Closure $config)
    {
        $this->prebuildConfig[$key] = $config;
    }

    public function addPostbuildConfig($key, Closure $config)
    {
        $this->postbuildConfig[$key] = $config;
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

    public function getOrFail()
    {
        $message = $this->get();
        $this->messageWasFetched = false;
        $message->validate();
        $this->messageWasFetched = true;

        return $message;
    }

    public function get()
    {
        if (!$this->messageIsEmpty()) {
            $this->writeSeg('unz', [$this->messageCount, $this->unbReference()]);
            $this->edifactFile->rewind();
        }

        $edifactObject = new $this->edifactClass($this->edifactFile);
        foreach ($this->postbuildConfig as $key => $postbuildConfig) {
            $edifactObject->addConfiguration($key, $postbuildConfig);
        }

        $this->messageWasFetched = true;

        return $edifactObject;
    }

    public function unbReference()
    {
        if (!$this->unbReference) {
            if (isset($this->prebuildConfig['unbReference'])) {
                return $this->unbReference = $this->prebuildConfig['unbReference']();
            }
            return $this->unbReference = uniqid();
        }
        return $this->unbReference;
    }

    public function unhCount()
    {
        return $this->unhCounter;
    }
    
    abstract protected function getMessageClass();

    abstract protected function writeUnb();

    abstract protected function writeMessage($array);

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
        $this->unhCounter++;
    }
    
    private function messageIsEmpty()
    {
        return $this->edifactFile->tell() == 0;
    }
}
