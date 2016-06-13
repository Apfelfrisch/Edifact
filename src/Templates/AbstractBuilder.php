<?php 

namespace Proengeno\Edifact\Templates;

use Closure;
use Proengeno\Edifact\Message\Message;
use Proengeno\Edifact\Message\EdifactFile;
use Proengeno\Edifact\Message\SegmentFactory;
use Proengeno\Edifact\Exceptions\EdifactException;

abstract class AbstractBuilder
{
    protected $to;
    protected $from;
    protected $edifactFile;
    protected $prebuildConfig = [];
    protected $postbuildConfig = [];

    private $edifactClass;
    private $unhCounter = 0;
    private $buildCache = [];
    private $messageCount = 0;
    private $messageWasFetched = false;
    
    public function __construct($from, $to, $filepath = null)
    {
        $this->to = $to;
        $this->from = $from;
        $this->edifactClass = $this->getMessageClass();
        $this->edifactFile = new EdifactFile($filepath ?: 'php://temp', 'w+');
    }

    public function __destruct()
    {
        // Delete File if build process could not finshed (Expetion, etc)
        if ($this->edifactFile) {
            $filepath = $this->edifactFile->getRealPath();
            if ($this->messageWasFetched === false && file_exists($filepath)) {
                unlink($filepath);
            }
        }
    }

    public function addPrebuildConfig($key, Closure $config)
    {
        if (!empty($this->buildCache)) {
            throw new EdifactException('Message already building, could not add PrebuildConfig');
        }

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
    }

    public function unbReference()
    {
        if (!isset($this->buildCache['unbReference'])) {
            if (isset($this->prebuildConfig['unbReference'])) {
                return $this->buildCache['unbReference'] = $this->prebuildConfig['unbReference']();
            }
            return $this->buildCache['unbReference'] = uniqid();
        }
        return $this->buildCache['unbReference'];
    }
    
    public function getSegmentFactory()
    {
        if (!isset($this->buildCache['segmentFactory'])) {
            if (isset($this->prebuildConfig['delimiter'])) {
                return $this->buildCache['segmentFactory'] = new SegmentFactory($this->prebuildConfig['delimiter']());
            }
            return $this->buildCache['segmentFactory'] = new SegmentFactory;
        }

        return $this->buildCache['segmentFactory'];
    }

    public function unhCount()
    {
        return $this->unhCounter;
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

        return new Message($edifactObject);
    }

    abstract protected function getMessageClass();

    abstract protected function writeUnb();

    abstract protected function writeMessage($array);

    protected function writeSeg($segment, $attributes = [], $method = 'fromAttributes')
    {
        $edifactClass = $this->edifactClass;
        $segment = $this->getSegmentFactory()->fromAttributes($edifactClass::getSegmentClass($segment), $attributes, $method);
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
